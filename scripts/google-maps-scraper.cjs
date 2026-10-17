#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const os = require('os');
const { execFileSync } = require('child_process');

/**
 * php artisan serve inherits its LISTEN socket into child processes.
 * Closing every FD >= 3 also kills libuv's kqueue and crashes Node with:
 *   Assertion failed: (errno == EINTR), function uv__io_poll, file kqueue.c
 * Only close inherited TCP listen sockets.
 */
function closeInheritedListenSockets() {
    try {
        const out = execFileSync(
            'lsof',
            ['-nP', '-p', String(process.pid), '-a', '-iTCP', '-sTCP:LISTEN'],
            { encoding: 'utf8', timeout: 2500, stdio: ['ignore', 'pipe', 'ignore'] }
        );

        for (const line of out.split('\n').slice(1)) {
            const parts = line.trim().split(/\s+/);
            if (parts.length < 4) {
                continue;
            }

            const fdNum = parseInt(parts[3], 10);
            if (! Number.isFinite(fdNum) || fdNum < 3) {
                continue;
            }

            try {
                fs.closeSync(fdNum);
            } catch {
                // already closed / not closable
            }
        }
    } catch {
        // lsof unavailable — skip rather than breaking the event loop
    }
}

closeInheritedListenSockets();

/**
 * Multi-source business scraper:
 * 1) Google Maps (main query + area expansions)
 * 2) Google Search / Local results as fallback enrichment
 *
 * Usage: node scripts/google-maps-scraper.cjs "query" [maxResults]
 * maxResults = 0 means unlimited (collect every unique result available).
 * Streams NDJSON progress/done/error lines.
 */

const { chromium } = require('playwright');

const query = process.argv[2];
const parsedMax = parseInt(process.argv[3] ?? '0', 10);
const maxResults = Number.isFinite(parsedMax) ? Math.max(0, parsedMax) : 0;
const unlimited = maxResults <= 0;

function underLimit(count) {
    return unlimited || count < maxResults;
}

function targetTotal(fallback = 0) {
    return unlimited ? fallback : maxResults;
}

if (!query) {
    emit({ type: 'error', message: 'Query is required' });
    process.exit(1);
}

const projectRoot = path.join(__dirname, '..');
const browsersPath = process.env.PLAYWRIGHT_BROWSERS_PATH
    || path.join(projectRoot, 'storage', 'app', 'playwright');
const profileDir = path.join(projectRoot, 'storage', 'app', 'playwright-profile');

process.env.PLAYWRIGHT_BROWSERS_PATH = browsersPath;
if (!process.env.HOME || process.env.HOME === '/') {
    process.env.HOME = os.homedir() || '/Users/macbookpro';
}
if (!process.env.TMPDIR) {
    process.env.TMPDIR = os.tmpdir();
}
fs.mkdirSync(profileDir, { recursive: true });
const tempDir = path.join(projectRoot, 'storage', 'app', 'temp');
fs.mkdirSync(tempDir, { recursive: true });
const outFile = path.join(tempDir, `scrape-${process.pid}-${Date.now()}.json`);

function saveCheckpoint(places) {
    try {
        fs.writeFileSync(outFile, JSON.stringify(places));
    } catch (_) {
        // ignore checkpoint failures
    }
}

const CITY_AREAS = {
    الرياض: [
        'العليا', 'الملز', 'النرجس', 'الياسمين', 'الروضة', 'السليمانية', 'قرطبة', 'المونسية', 'الرمال', 'الشفا',
        'الربوة', 'الملقا', 'حطين', 'الورود', 'العزيزية', 'الياسمين', 'الصحافة', 'الغدير', 'النفل', 'الندى',
        'الربيع', 'التعاون', 'الإزدهار', 'اليرموك', 'الخليج', 'النهضة', 'المنار', 'الجزيرة', 'السعادة', 'الفيصلية',
        'المروج', 'اشبيلية', 'القيروان', 'العارض', 'بنبان', 'طويق', 'العقيق', 'الدرعية',
    ],
    جدة: ['الحمراء', 'الروضة', 'أبحر', 'الشاطئ', 'الزهراء', 'البلد', 'الفيصلية', 'أبحر الشمالية', 'البوادي', 'السلامة', 'النعيم', 'الربوة'],
    الدمام: ['الجلوية', 'الشاطئ', 'الفيصلية', 'النور', 'الراكة', 'الزيتون', 'الجلوية', 'أحد', 'الطبيشي'],
    الخبر: ['العقربية', 'الثقافة', 'اليارموك', 'الحزام الذهبي', 'الكورنيش', 'الجسر'],
    المدينة: ['قباء', 'العوالي', 'الحرة الشرقية', 'الملك فهد', 'الدفاع', 'الجرف'],
    مكة: ['العزيزية', 'الشوقية', 'العوالي', 'الرصيفة', 'الشرائع', 'النوارية'],
};

function emit(payload) {
    process.stdout.write(`${JSON.stringify(payload)}\n`);
}

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

const stopFile = process.env.SCRAPE_STOP_FILE || '';

function shouldStop() {
    if (!stopFile) {
        return false;
    }

    try {
        return fs.existsSync(stopFile);
    } catch {
        return false;
    }
}

function normalizeName(name) {
    return String(name || '')
        .toLowerCase()
        .replace(/[^\p{L}\p{N}\s]/gu, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function normalizePhone(phone) {
    const digits = String(phone || '').replace(/\D+/g, '');
    if (!digits) return '';
    if (digits.startsWith('966')) return digits.slice(-9);
    if (digits.startsWith('0') && digits.length >= 9) return digits.slice(-9);
    return digits.slice(-9);
}

function placeKey(place) {
    const phone = normalizePhone(place.phone);
    if (phone) return `p:${phone}`;
    return `n:${normalizeName(place.name)}`;
}

function buildQueries(baseQuery) {
    const queries = [baseQuery];
    const foundCity = Object.keys(CITY_AREAS).find((city) => baseQuery.includes(city));
    if (!foundCity) return queries;

    let keyword = baseQuery
        .replace(foundCity, ' ')
        .replace(/\bفي\b/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    if (!keyword) keyword = baseQuery;

    for (const area of CITY_AREAS[foundCity]) {
        queries.push(`${keyword} ${area} ${foundCity}`);
    }

    return [...new Set(queries)];
}

function findFullChromium() {
    try {
        const root = path.join(browsersPath, 'chromium-1243', 'chrome-mac-x64');
        const candidates = [
            path.join(root, 'Google Chrome for Testing.app', 'Contents', 'MacOS', 'Google Chrome for Testing'),
            path.join(root, 'Chromium.app', 'Contents', 'MacOS', 'Chromium'),
            path.join(root, 'chrome'),
        ];
        for (const candidate of candidates) {
            if (fs.existsSync(candidate)) return candidate;
        }
    } catch (_) {}
    return null;
}

async function launchBrowser() {
    const sharedArgs = [
        '--disable-blink-features=AutomationControlled',
        '--no-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--disable-software-rasterizer',
        '--no-first-run',
        '--no-default-browser-check',
    ];

    const attempts = [
        { options: { channel: 'chrome', headless: true, args: sharedArgs } },
        { options: { executablePath: findFullChromium() || undefined, headless: true, args: sharedArgs } },
        { options: { headless: true, args: sharedArgs } },
    ];

    let lastError;
    for (const attempt of attempts) {
        if (Object.prototype.hasOwnProperty.call(attempt.options, 'executablePath') && !attempt.options.executablePath) {
            continue;
        }
        try {
            return await chromium.launch(attempt.options);
        } catch (error) {
            lastError = error;
        }
    }
    throw lastError || new Error('Unable to launch browser');
}

async function acceptConsent(page) {
    const selectors = [
        'button:has-text("Accept all")',
        'button:has-text("I agree")',
        'button:has-text("Accept")',
        'button:has-text("موافق")',
        'button:has-text("قبول الكل")',
        'form[action*="consent"] button',
    ];
    for (const selector of selectors) {
        try {
            const btn = page.locator(selector).first();
            if (await btn.isVisible({ timeout: 1200 })) {
                await btn.click({ timeout: 2000 });
                await sleep(600);
                return;
            }
        } catch (_) {}
    }
}

async function scrollFeed(page, targetCount, onProgress) {
    const feed = page.locator('div[role="feed"]').first();
    if (!(await feed.count())) return;

    let stableRounds = 0;
    let lastCount = 0;

    for (let i = 0; i < 80; i++) {
        if (shouldStop()) break;

        const links = await page.locator('a[href*="/maps/place/"]').count();
        onProgress?.(links);
        if (links >= targetCount) break;

        await feed.evaluate((el) => el.scrollBy(0, el.scrollHeight)).catch(async () => {
            await page.mouse.wheel(0, 5000);
        });
        await sleep(900);

        if (await page.locator('text=/You\'ve reached the end|لقد وصلت إلى نهاية|نهاية القائمة/i').count()) {
            break;
        }

        if (links === lastCount) {
            stableRounds += 1;
            if (stableRounds >= 6) break;
        } else {
            stableRounds = 0;
            lastCount = links;
        }
    }
}

async function collectMapLinks(page, searchQuery, wantCount, onListed) {
    const url = `https://www.google.com/maps/search/${encodeURIComponent(searchQuery)}?hl=ar`;
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await sleep(1800);
    await acceptConsent(page);

    await Promise.race([
        page.waitForSelector('div[role="feed"]', { timeout: 15000 }),
        page.waitForSelector('h1', { timeout: 15000 }),
    ]).catch(() => {});

    await scrollFeed(page, wantCount, onListed);

    const hrefs = await page.$$eval('a[href*="/maps/place/"]', (anchors) => {
        const seen = new Set();
        const urls = [];
        for (const a of anchors) {
            const href = (a.href || '').split('&')[0];
            if (!href || seen.has(href)) continue;
            seen.add(href);
            urls.push(href);
        }
        return urls;
    });

    if (!hrefs.length && page.url().includes('/maps/place/')) {
        return [page.url().split('&')[0]];
    }

    return hrefs;
}

async function extractPlace(context, href) {
    const page = await context.newPage();
    try {
        await page.goto(href, { waitUntil: 'domcontentloaded', timeout: 40000 });
        await sleep(1000);
        await acceptConsent(page);

        const name = (await page.locator('h1').first().textContent({ timeout: 7000 }).catch(() => ''))?.trim() || '';

        let phone = '';
        const phoneBtn = page.locator('button[data-item-id^="phone:tel:"]').first();
        if (await phoneBtn.count()) {
            const itemId = await phoneBtn.getAttribute('data-item-id');
            if (itemId && itemId.includes('phone:tel:')) {
                phone = decodeURIComponent(itemId.replace('phone:tel:', '')).trim();
            }
            if (!phone) {
                phone = (await phoneBtn.getAttribute('aria-label') || '')
                    .replace(/phone:?/i, '')
                    .replace(/الهاتف:?/i, '')
                    .trim();
            }
        }

        let website = '';
        const websiteLink = page.locator('a[data-item-id="authority"]').first();
        if (await websiteLink.count()) {
            website = (await websiteLink.getAttribute('href')) || '';
        }

        if (!name) return null;
        return { name, phone, website, source: 'google_maps' };
    } catch (_) {
        return null;
    } finally {
        await page.close().catch(() => {});
    }
}

async function scrapeGoogleSearch(context, searchQuery, already, onProgress) {
    const page = await context.newPage();
    const results = [];
    try {
        // Keep paging until Google stops returning results (or soft page ceiling).
        for (let start = 0; start < 200 && underLimit(already + results.length); start += 10) {
            const url = `https://www.google.com/search?q=${encodeURIComponent(searchQuery)}&hl=ar&gl=sa&start=${start}`;
            await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
            await sleep(1200);
            await acceptConsent(page);

            const pageItems = await page.evaluate(() => {
                const items = [];
                const blocks = document.querySelectorAll('div.MjjYud, div.g, div[data-sokoban-container]');
                for (const block of blocks) {
                    const titleEl = block.querySelector('h3');
                    const name = titleEl?.textContent?.trim() || '';
                    if (!name || name.length < 3) continue;

                    const text = block.textContent || '';
                    const phoneMatch = text.match(/(?:\+966|00966|0)?(?:5\d{8}|1\d{8}|9200\d{5}|\d{9,10})/);
                    const link = block.querySelector('a[href^="http"]')?.href || '';
                    const skipHosts = ['google.', 'youtube.', 'facebook.', 'instagram.', 'twitter.', 'x.com', 'linkedin.'];
                    if (skipHosts.some((h) => link.includes(h))) continue;

                    items.push({
                        name,
                        phone: phoneMatch ? phoneMatch[0] : '',
                        website: link,
                        source: 'google_search',
                    });
                }
                return items;
            });

            if (!pageItems.length) break;

            let added = 0;
            for (const item of pageItems) {
                if (!underLimit(already + results.length)) break;
                results.push(item);
                added += 1;
            }

            onProgress?.(already + results.length, `Google Search: تم جمع ${already + results.length} نتيجة محتملة...`);
            if (added === 0) break;
            await sleep(700);
        }
    } catch (_) {
        // ignore search failures; maps data still returns
    } finally {
        await page.close().catch(() => {});
    }
    return results;
}

function mergeUnique(places) {
    const map = new Map();
    for (const place of places) {
        if (!place?.name) continue;
        const key = placeKey(place);
        if (!map.has(key)) {
            map.set(key, place);
            continue;
        }
        const existing = map.get(key);
        map.set(key, {
            name: existing.name || place.name,
            phone: existing.phone || place.phone || '',
            website: existing.website || place.website || '',
            source: existing.source || place.source || '',
        });
    }
    return [...map.values()];
}

function calcPercent(areaIndex, areasTotal, itemIndex, itemsTotal) {
    if (areasTotal <= 0) return 1;
    const areaWeight = 90 / areasTotal;
    const within = itemsTotal > 0 ? (itemIndex / itemsTotal) : 0;
    return Math.max(1, Math.min(99, Math.round(areaIndex * areaWeight + within * areaWeight)));
}

(async () => {
    let browser;
    try {
        const queries = buildQueries(query);
        const perAreaLinkCap = 50; // prevent endless link-hoarding per area
        const seenLinks = new Set();
        const extracted = [];
        const seenPlaceKeys = new Set();
        let emptyAreasStreak = 0;

        emit({
            type: 'progress',
            phase: 'launching',
            message: `جاري فتح المتصفح... معالجة منطقة بمنطقة مع حفظ فوري لكل شركة (${queries.length} منطقة)`,
            found: 0,
            listed: 0,
            total: queries.length,
            percent: 1,
            unlimited,
        });

        browser = await launchBrowser();
        const context = await browser.newContext({
            locale: 'ar',
            userAgent:
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            viewport: { width: 1365, height: 900 },
        });

        const page = await context.newPage();
        let userStopped = false;

        for (let qi = 0; qi < queries.length; qi++) {
            if (shouldStop()) {
                userStopped = true;
                break;
            }
            if (!underLimit(extracted.length)) break;

            const q = queries[qi];
            let newInArea = 0;

            emit({
                type: 'progress',
                phase: 'searching',
                message: `المنطقة ${qi + 1}/${queries.length}: ${q}`,
                found: extracted.length,
                listed: seenLinks.size,
                total: queries.length,
                percent: calcPercent(qi, queries.length, 0, 1),
                unlimited,
            });

            const links = await collectMapLinks(page, q, perAreaLinkCap, (listed) => {
                emit({
                    type: 'progress',
                    phase: 'scrolling',
                    message: `جمع روابط المنطقة ${qi + 1}/${queries.length}... ظاهر: ${listed}`,
                    found: extracted.length,
                    listed: seenLinks.size,
                    feed: listed,
                    total: queries.length,
                    percent: calcPercent(qi, queries.length, Math.min(listed, perAreaLinkCap), perAreaLinkCap * 2),
                    unlimited,
                });
            });

            if (shouldStop()) {
                userStopped = true;
                break;
            }

            const freshLinks = [];
            for (const href of links) {
                if (seenLinks.has(href)) continue;
                seenLinks.add(href);
                freshLinks.push(href);
                if (freshLinks.length >= perAreaLinkCap) break;
            }

            if (freshLinks.length === 0) {
                emptyAreasStreak += 1;
                // If several areas add nothing new, stop early.
                if (emptyAreasStreak >= 4 && extracted.length > 0) {
                    emit({
                        type: 'progress',
                        phase: 'searching',
                        message: 'لا توجد شركات جديدة في عدة مناطق متتالية — سيتم الإنهاء.',
                        found: extracted.length,
                        listed: seenLinks.size,
                        percent: 92,
                        unlimited,
                    });
                    break;
                }
                continue;
            }

            emptyAreasStreak = 0;

            for (let i = 0; i < freshLinks.length; i++) {
                if (shouldStop()) {
                    userStopped = true;
                    break;
                }
                if (!underLimit(extracted.length)) break;

                const place = await extractPlace(context, freshLinks[i]);
                if (place) {
                    const key = placeKey(place);
                    if (!seenPlaceKeys.has(key)) {
                        seenPlaceKeys.add(key);
                        extracted.push(place);
                        newInArea += 1;
                        emit({ type: 'place', place });
                        saveCheckpoint(extracted);
                    }
                }

                emit({
                    type: 'progress',
                    phase: 'extracting',
                    message: place
                        ? `حفظ فوري: ${place.name} | الإجمالي المستخرج: ${extracted.length}`
                        : `استخراج ${i + 1}/${freshLinks.length} في المنطقة ${qi + 1}/${queries.length}`,
                    found: extracted.length,
                    listed: seenLinks.size,
                    total: queries.length,
                    index: i + 1,
                    current: place?.name || null,
                    percent: calcPercent(qi, queries.length, i + 1, freshLinks.length),
                    unlimited,
                });

                await sleep(200);
            }

            if (userStopped) {
                break;
            }

            emit({
                type: 'progress',
                phase: 'searching',
                message: `انتهت المنطقة ${qi + 1}/${queries.length}: +${newInArea} شركة جديدة | الإجمالي: ${extracted.length}`,
                found: extracted.length,
                listed: seenLinks.size,
                percent: calcPercent(qi + 1, queries.length, 0, 1),
                unlimited,
            });
        }

        // Optional enrichment from Google Search (also one-by-one).
        if (!userStopped && underLimit(extracted.length)) {
            emit({
                type: 'progress',
                phase: 'searching',
                message: `Google Search لمصادر إضافية... المستخرج حتى الآن: ${extracted.length}`,
                found: extracted.length,
                listed: seenLinks.size,
                percent: 93,
                unlimited,
            });

            const searchItems = await scrapeGoogleSearch(
                context,
                query,
                extracted.length,
                (count, message) => {
                    emit({
                        type: 'progress',
                        phase: 'searching',
                        message,
                        found: extracted.length,
                        listed: count,
                        percent: Math.min(97, 93 + Math.floor((count % 20))),
                        unlimited,
                    });
                }
            );

            for (const item of searchItems) {
                if (shouldStop()) {
                    userStopped = true;
                    break;
                }
                if (!underLimit(extracted.length)) break;
                const key = placeKey(item);
                if (seenPlaceKeys.has(key)) continue;
                seenPlaceKeys.add(key);
                extracted.push(item);
                emit({ type: 'place', place: item });
            }
        }

        if (shouldStop()) {
            userStopped = true;
        }

        const merged = unlimited ? extracted : extracted.slice(0, maxResults);
        saveCheckpoint(merged);

        if (userStopped) {
            emit({
                type: 'progress',
                phase: 'stopping',
                message: `تم الإيقاف. جاري اعتماد ${merged.length} نتيجة تم جمعها...`,
                found: merged.length,
                listed: merged.length,
                total: merged.length || 1,
                percent: 96,
                unlimited,
                stopped: true,
            });
        } else {
            emit({
                type: 'progress',
                phase: 'extracting',
                message: `اكتمل الجمع: ${merged.length} شركة فريدة`,
                found: merged.length,
                listed: merged.length,
                total: merged.length || 1,
                percent: 98,
                unlimited,
            });
        }

        emit({ type: 'done', file: outFile, count: merged.length, stopped: userStopped });
    } catch (error) {
        process.stderr.write(String(error?.stack || error));
        try {
            if (fs.existsSync(outFile)) {
                const partial = JSON.parse(fs.readFileSync(outFile, 'utf8'));
                if (Array.isArray(partial) && partial.length) {
                    emit({
                        type: 'progress',
                        phase: 'extracting',
                        message: `حدث خطأ، لكن سيتم اعتماد ${partial.length} نتيجة تم جمعها`,
                        found: partial.length,
                        listed: partial.length,
                        total: partial.length,
                        percent: 95,
                    });
                    emit({ type: 'done', file: outFile, count: partial.length, partial: true });
                    process.exitCode = 0;
                    return;
                }
            }
        } catch (_) {}
        emit({ type: 'error', message: error.message || 'Scrape failed' });
        process.exitCode = 1;
    } finally {
        if (browser) await browser.close().catch(() => {});
    }
})();
