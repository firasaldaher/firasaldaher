/**
 * ============================================================
 *  Caraway — Advanced Visitor Intelligence Engine v2.0
 *  يجمع كل المعلومات المتاحة عن الزائر عبر المتصفح
 * ============================================================
 */

const VisitorIntel = (() => {
    'use strict';

    /* =========================================================
       1.  DEVICE & HARDWARE
    ========================================================= */
    function getDevice() {
        const ua = navigator.userAgent;
        const p  = navigator.platform || '';

        // Device type
        const isMobile  = /Mobi|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);
        const isTablet  = /(iPad|tablet|Tablet|PlayBook)/i.test(ua) || (isMobile && window.screen.width >= 600);
        const deviceType = isTablet ? 'Tablet' : isMobile ? 'Mobile' : 'Desktop';

        // OS
        let os = 'Unknown OS';
        if (/Windows NT 10/.test(ua)) os = 'Windows 10/11';
        else if (/Windows NT 6\.3/.test(ua)) os = 'Windows 8.1';
        else if (/Windows NT 6\.2/.test(ua)) os = 'Windows 8';
        else if (/Windows NT 6\.1/.test(ua)) os = 'Windows 7';
        else if (/Windows/.test(ua)) os = 'Windows';
        else if (/Mac OS X/.test(ua)) { os = 'macOS ' + (ua.match(/Mac OS X ([^)]+)/) || ['',''])[1].replace(/_/g,'.'); }
        else if (/Android/.test(ua))  os = 'Android ' + (ua.match(/Android\s([0-9.]+)/) || ['',''])[1];
        else if (/iPhone|iPad|iPod/.test(ua)) os = 'iOS ' + (ua.match(/OS\s([0-9_]+)/) || ['',''])[1].replace(/_/g,'.');
        else if (/Linux/.test(ua))    os = 'Linux';
        else if (/CrOS/.test(ua))     os = 'ChromeOS';

        // Device brand / model
        let brand = 'Unknown';
        const androidMatch = ua.match(/\(Linux; Android [^;]+; ([^)]+)\)/);
        if (androidMatch) brand = androidMatch[1].trim();
        else if (/iPhone/.test(ua)) brand = 'Apple iPhone';
        else if (/iPad/.test(ua))   brand = 'Apple iPad';
        else if (/Mac/.test(ua))    brand = 'Apple Mac';

        // CPU / Architecture
        let cpu = 'Unknown';
        if (/Win64|x64|WOW64|x86_64|amd64/i.test(ua + p)) cpu = 'x86-64 (64-bit)';
        else if (/Win32|i686|i386/i.test(ua + p))          cpu = 'x86 (32-bit)';
        else if (/arm64|aarch64/i.test(ua + p))            cpu = 'ARM64';
        else if (/arm/i.test(ua + p))                      cpu = 'ARM';

        // Logical CPU cores
        const cores = navigator.hardwareConcurrency || 'Unknown';

        // RAM (GB) — Chrome/Edge only
        const ram = navigator.deviceMemory ? navigator.deviceMemory + ' GB' : 'Not disclosed';

        return { deviceType, os, brand, cpu, cores, ram };
    }

    /* =========================================================
       2.  SCREEN & DISPLAY
    ========================================================= */
    function getScreen() {
        const sc = window.screen;
        const dpr = window.devicePixelRatio || 1;
        const colorDepth = sc.colorDepth || sc.pixelDepth || 'Unknown';

        // Physical vs logical resolution
        const physW = Math.round(sc.width  * dpr);
        const physH = Math.round(sc.height * dpr);

        // Viewport
        const vpW = window.innerWidth  || document.documentElement.clientWidth;
        const vpH = window.innerHeight || document.documentElement.clientHeight;

        // Orientation
        const orientation = (sc.orientation && sc.orientation.type)
            || (window.innerWidth > window.innerHeight ? 'landscape' : 'portrait');

        // Touch
        const touchPoints = navigator.maxTouchPoints || 0;
        const isTouch = touchPoints > 0;

        // HDR / color gamut (Chrome 79+)
        let colorGamut = 'Unknown';
        if (window.matchMedia) {
            if (window.matchMedia('(color-gamut: rec2020)').matches) colorGamut = 'Rec.2020';
            else if (window.matchMedia('(color-gamut: p3)').matches)  colorGamut = 'P3';
            else if (window.matchMedia('(color-gamut: srgb)').matches) colorGamut = 'sRGB';
        }

        // Dark mode preference
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

        // Reduced motion preference
        const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        return {
            screenResolution: `${sc.width}x${sc.height}`,
            physicalResolution: `${physW}x${physH}`,
            viewport: `${vpW}x${vpH}`,
            devicePixelRatio: dpr.toFixed(2),
            colorDepth: colorDepth + '-bit',
            colorGamut,
            orientation,
            touchPoints,
            isTouch,
            prefersDark,
            prefersReducedMotion
        };
    }

    /* =========================================================
       3.  BROWSER & ENGINE
    ========================================================= */
    function getBrowser() {
        const ua = navigator.userAgent;

        // Browser name & version
        let name = 'Unknown', version = '';
        const rules = [
            [/Edg\/([0-9.]+)/,        'Microsoft Edge'],
            [/OPR\/([0-9.]+)/,        'Opera'],
            [/Opera\/([0-9.]+)/,      'Opera'],
            [/SamsungBrowser\/([0-9.]+)/, 'Samsung Browser'],
            [/UCBrowser\/([0-9.]+)/,  'UC Browser'],
            [/YaBrowser\/([0-9.]+)/,  'Yandex Browser'],
            [/Brave\/([0-9.]+)/,      'Brave'],
            [/CriOS\/([0-9.]+)/,      'Chrome (iOS)'],
            [/FxiOS\/([0-9.]+)/,      'Firefox (iOS)'],
            [/Chrome\/([0-9.]+)/,     'Google Chrome'],
            [/Firefox\/([0-9.]+)/,    'Mozilla Firefox'],
            [/Safari\/([0-9.]+)/,     'Apple Safari'],
            [/MSIE\s([0-9.]+)/,       'Internet Explorer'],
            [/Trident.*rv:([0-9.]+)/, 'Internet Explorer'],
        ];
        for (const [rx, n] of rules) {
            const m = ua.match(rx);
            if (m) { name = n; version = m[1]; break; }
        }

        // Rendering engine
        let engine = 'Unknown';
        if (/Gecko\/[0-9]/.test(ua) && !/like Gecko/.test(ua)) engine = 'Gecko';
        else if (/AppleWebKit/.test(ua)) engine = 'Blink/WebKit';
        else if (/Trident/.test(ua))     engine = 'Trident';
        else if (/Presto/.test(ua))      engine = 'Presto';

        // Language
        const language = navigator.language || navigator.userLanguage || 'Unknown';
        const languages = (navigator.languages || [language]).join(', ');

        // Timezone
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Unknown';
        const tzOffset  = -new Date().getTimezoneOffset(); // in minutes, positive = ahead UTC
        const tzStr     = `UTC${tzOffset >= 0 ? '+' : ''}${tzOffset / 60}`;

        // Locale / Date format
        const locale = Intl.DateTimeFormat().resolvedOptions().locale || language;

        // Online status
        const online = navigator.onLine;

        // Connection info (Network Information API — Chrome/Android)
        let connection = {};
        const nc = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (nc) {
            connection = {
                type:         nc.type || 'Unknown',
                effectiveType: nc.effectiveType || 'Unknown',   // 4g / 3g / 2g / slow-2g
                downlink:     nc.downlink ? nc.downlink + ' Mbps' : 'Unknown',
                rtt:          nc.rtt     ? nc.rtt     + ' ms'   : 'Unknown',
                saveData:     nc.saveData || false
            };
        }

        // Cookies & Storage support
        const cookiesEnabled = navigator.cookieEnabled;
        const localStorageOk = (() => { try { localStorage.setItem('_t','1'); localStorage.removeItem('_t'); return true; } catch(_){ return false; } })();
        const sessionStorageOk = (() => { try { sessionStorage.setItem('_t','1'); sessionStorage.removeItem('_t'); return true; } catch(_){ return false; } })();
        const indexedDBOk = !!window.indexedDB;

        // Plugins (non-empty only)
        const plugins = Array.from(navigator.plugins || []).map(p => p.name).filter(Boolean).slice(0, 10);

        // Java / PDF / Flash
        const javaEnabled = navigator.javaEnabled ? navigator.javaEnabled() : false;

        // Do Not Track
        const dnt = navigator.doNotTrack || window.doNotTrack || navigator.msDoNotTrack || 'Unknown';

        // WebGL renderer (GPU name)
        let gpu = 'Unknown';
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (gl) {
                const ext = gl.getExtension('WEBGL_debug_renderer_info');
                if (ext) {
                    gpu = gl.getParameter(ext.UNMASKED_RENDERER_WEBGL) || 'Unknown';
                }
            }
        } catch(_) {}

        // Canvas fingerprint (hash only — not raw data)
        let canvasHash = 'unavailable';
        try {
            const cv = document.createElement('canvas');
            cv.width = 200; cv.height = 50;
            const ctx = cv.getContext('2d');
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillStyle = '#f60';
            ctx.fillRect(125, 1, 62, 20);
            ctx.fillStyle = '#069';
            ctx.fillText('CarawayFP', 2, 15);
            ctx.fillStyle = 'rgba(102,204,0,0.7)';
            ctx.fillText('CarawayFP', 4, 17);
            const data = cv.toDataURL();
            let h = 0;
            for (let i = 0; i < data.length; i++) { h = (Math.imul(31, h) + data.charCodeAt(i)) | 0; }
            canvasHash = Math.abs(h).toString(16);
        } catch(_) {}

        // Audio fingerprint (oscillator characteristics)
        let audioHash = 'unavailable';
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) {
                const actx = new AudioCtx();
                const osc  = actx.createOscillator();
                const analyser = actx.createAnalyser();
                const gain = actx.createGain();
                gain.gain.value = 0;
                osc.connect(analyser); analyser.connect(gain); gain.connect(actx.destination);
                osc.start(0);
                const buf = new Float32Array(analyser.frequencyBinCount);
                analyser.getFloatFrequencyData(buf);
                osc.stop(); actx.close();
                const sum = buf.slice(0,50).reduce((a,b) => a + Math.abs(b), 0);
                audioHash = sum.toFixed(4);
            }
        } catch(_) {}

        return {
            name, version, engine,
            language, languages,
            timezone, tzStr, locale,
            online, connection,
            cookiesEnabled, localStorageOk, sessionStorageOk, indexedDBOk,
            plugins, javaEnabled, dnt,
            gpu, canvasHash, audioHash,
            userAgent: ua
        };
    }

    /* =========================================================
       4.  PAGE & SESSION
    ========================================================= */
    function getPage() {
        const now = new Date();
        return {
            url:       location.href,
            path:      location.pathname,
            hostname:  location.hostname,
            referrer:  document.referrer || 'Direct / No referrer',
            title:     document.title,
            visitTime: now.toISOString(),
            localTime: now.toLocaleString(),
            dayOfWeek: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][now.getDay()],
            sessionId: getOrCreateSession()
        };
    }

    function getOrCreateSession() {
        let sid = sessionStorage.getItem('_caraway_sid');
        if (!sid) {
            sid = Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('_caraway_sid', sid);
        }
        return sid;
    }

    /* =========================================================
       5.  PUBLIC IP (via free APIs — tries 3 services)
    ========================================================= */
    async function fetchIP() {
        const apis = [
            { url: 'https://api.ipify.org?format=json',         parse: d => ({ ip: d.ip }) },
            { url: 'https://ipapi.co/json/',                    parse: d => ({ ip: d.ip, city: d.city, region: d.region, country: d.country_name, isp: d.org, lat: d.latitude, lon: d.longitude }) },
            { url: 'https://freeipapi.com/api/json',            parse: d => ({ ip: d.ipAddress, city: d.cityName, country: d.countryName, lat: d.latitude, lon: d.longitude }) },
        ];
        for (const api of apis) {
            try {
                const res = await fetch(api.url, { cache: 'no-store' });
                if (!res.ok) continue;
                return api.parse(await res.json());
            } catch(_) {}
        }
        return { ip: 'Unable to fetch', city: '', country: '', isp: '' };
    }

    /* =========================================================
       6.  BEHAVIOR TRACKING (Dwell, Scroll, Clicks, Mouse)
    ========================================================= */
    const behavior = {
        pageLoadTime: Date.now(),
        maxScrollPct: 0,
        clicks: 0,
        keystrokes: 0,
        mouseMovements: 0,
        copyEvents: 0,
        focusLostCount: 0,
        sectionsViewed: {},
        activeSections: {},
        clickLog: []
    };

    function initBehavior() {
        // Scroll depth
        window.addEventListener('scroll', () => {
            const scrolled = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
            if (scrolled > behavior.maxScrollPct) behavior.maxScrollPct = Math.min(scrolled, 100);
        }, { passive: true });

        // Click count + log
        document.addEventListener('click', e => {
            behavior.clicks++;
            const el = e.target;
            behavior.clickLog.push({
                tag:  el.tagName,
                id:   el.id || '',
                cls:  el.className.toString().split(' ')[0] || '',
                text: (el.innerText || el.value || '').slice(0, 40),
                t:    Date.now() - behavior.pageLoadTime
            });
            if (behavior.clickLog.length > 50) behavior.clickLog.shift();
        });

        // Keystrokes (count only — never content)
        document.addEventListener('keydown', () => { behavior.keystrokes++; });

        // Mouse movement (sampled every 2s)
        let mmTimer;
        document.addEventListener('mousemove', () => {
            clearTimeout(mmTimer);
            mmTimer = setTimeout(() => { behavior.mouseMovements++; }, 2000);
        }, { passive: true });

        // Copy events
        document.addEventListener('copy', () => { behavior.copyEvents++; });

        // Tab focus lost
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) behavior.focusLostCount++;
        });

        // Section dwell time
        const sections = document.querySelectorAll('section[id]');
        if (sections.length && window.IntersectionObserver) {
            const obs = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    const id = entry.target.id;
                    if (entry.isIntersecting) {
                        behavior.activeSections[id] = Date.now();
                    } else if (behavior.activeSections[id]) {
                        behavior.sectionsViewed[id] = (behavior.sectionsViewed[id] || 0)
                            + (Date.now() - behavior.activeSections[id]) / 1000;
                        delete behavior.activeSections[id];
                    }
                });
            }, { threshold: 0.4 });
            sections.forEach(s => obs.observe(s));
        }

        // Save on unload
        window.addEventListener('beforeunload', () => {
            Object.keys(behavior.activeSections).forEach(id => {
                behavior.sectionsViewed[id] = (behavior.sectionsViewed[id] || 0)
                    + (Date.now() - behavior.activeSections[id]) / 1000;
            });
        });
    }

    /* =========================================================
       7.  FULL PROFILE BUILDER
    ========================================================= */
    async function buildProfile() {
        const [device, screen, browser, page, ipData] = await Promise.all([
            Promise.resolve(getDevice()),
            Promise.resolve(getScreen()),
            Promise.resolve(getBrowser()),
            Promise.resolve(getPage()),
            fetchIP()
        ]);

        return {
            timestamp: new Date().toISOString(),
            // -- IP & Geo --
            ip:       ipData,
            // -- Device --
            device,
            // -- Display --
            screen,
            // -- Browser --
            browser,
            // -- Page --
            page,
            // -- Behavior (live snapshot) --
            behavior: {
                dwellSec:     Math.round((Date.now() - behavior.pageLoadTime) / 1000),
                maxScrollPct: Math.round(behavior.maxScrollPct),
                clicks:       behavior.clicks,
                keystrokes:   behavior.keystrokes,
                mouseMovements: behavior.mouseMovements,
                copyEvents:   behavior.copyEvents,
                focusLostCount: behavior.focusLostCount,
                sectionsViewed: behavior.sectionsViewed,
                recentClicks:   behavior.clickLog.slice(-10)
            }
        };
    }

    /* =========================================================
       8.  SEND TO BACKEND  (fire-and-forget)
    ========================================================= */
    async function sendToBackend(profile) {
        // غيّر هذا إلى endpoint الخادم الخاص بك
        const ENDPOINT = '/api/visitor/collect.php';
        try {
            await fetch(ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(profile),
                keepalive: true
            });
        } catch(_) {
            // Silent — never interrupt the user experience
        }
    }

    /* =========================================================
       9.  LOCAL STORAGE — persist across sessions
    ========================================================= */
    function persistProfile(profile) {
        try {
            const history = JSON.parse(localStorage.getItem('_cw_visits') || '[]');
            history.push({ ts: profile.timestamp, page: profile.page.path });
            if (history.length > 30) history.shift();
            localStorage.setItem('_cw_visits', JSON.stringify(history));
            localStorage.setItem('_cw_device', JSON.stringify({
                type: profile.device.deviceType,
                os:   profile.device.os,
                browser: profile.browser.name + ' ' + profile.browser.version
            }));
        } catch(_) {}
    }

    /* =========================================================
       10. LEGACY AnalyticsEngine COMPATIBILITY
           (keeps existing cookie banner & getVisitorProfile working)
    ========================================================= */
    const LegacyEngine = {
        data: { sectionsViewed: {}, clicks: {}, consentGiven: false },
        activeSections: {},

        init() {
            const savedData = localStorage.getItem('caraway_analytics_profile');
            const consent   = localStorage.getItem('caraway_cookie_consent');
            if (consent === 'true') {
                this.data.consentGiven = true;
                this.hideCookieBanner();
                if (savedData) {
                    try { const p = JSON.parse(savedData); this.data.sectionsViewed = p.sectionsViewed||{}; this.data.clicks = p.clicks||{}; } catch(_){}
                }
            } else if (consent === 'false') {
                this.hideCookieBanner();
            }
            this.setupCookieBanner();
            window.getVisitorProfile = this.getVisitorProfile.bind(this);
        },

        saveData() {
            if (this.data.consentGiven) {
                localStorage.setItem('caraway_analytics_profile', JSON.stringify({ sectionsViewed: this.data.sectionsViewed, clicks: this.data.clicks }));
            }
        },

        setupCookieBanner() {
            const a = document.getElementById('accept-cookies');
            const d = document.getElementById('decline-cookies');
            if (a) a.addEventListener('click', () => { this.data.consentGiven = true; localStorage.setItem('caraway_cookie_consent','true'); this.hideCookieBanner(); this.saveData(); });
            if (d) d.addEventListener('click', () => { this.data.consentGiven = false; localStorage.setItem('caraway_cookie_consent','false'); this.hideCookieBanner(); });
        },

        hideCookieBanner() { const b = document.getElementById('cookie-banner'); if (b) b.style.display='none'; },

        getVisitorProfile() {
            if (!this.data.consentGiven) return "Analytics Disabled (No Consent)";
            let top = "None", max = 0;
            for (const [s,t] of Object.entries(this.data.sectionsViewed)) { if (t>max){max=t;top=s;} }
            return `--- Visitor Analytics Profile ---\nTop Section: ${top} (${Math.round(max)}s)\nSections: ${JSON.stringify(this.data.sectionsViewed)}\nClicks: ${JSON.stringify(this.data.clicks)}`;
        }
    };

    /* =========================================================
       11. INIT — called on DOMContentLoaded
    ========================================================= */
    async function init() {
        initBehavior();
        LegacyEngine.init();

        // Collect immediately
        const profile = await buildProfile();
        persistProfile(profile);

        // Send to backend (non-blocking)
        sendToBackend(profile);

        // Expose globally
        window.CarawayIntel = {
            getProfile:    () => buildProfile(),
            getSnapshot:   () => ({ ...profile, behavior: {
                dwellSec: Math.round((Date.now() - behavior.pageLoadTime) / 1000),
                maxScrollPct: Math.round(behavior.maxScrollPct),
                clicks: behavior.clicks, keystrokes: behavior.keystrokes
            }}),
            printReport:   () => buildProfile().then(p => console.table(p)),
            getVisitHistory: () => JSON.parse(localStorage.getItem('_cw_visits') || '[]')
        };

        // Also send an updated profile 30s after page load (has more behavior data)
        setTimeout(async () => {
            sendToBackend(await buildProfile());
        }, 30000);
    }

    return { init };
})();

// Boot
document.addEventListener('DOMContentLoaded', () => VisitorIntel.init());

// Also expose as AnalyticsEngine for any legacy code that imports it by name
const AnalyticsEngine = { init: () => {} }; // stub — real init done above