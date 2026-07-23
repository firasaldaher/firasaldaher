/**
 * Advanced Behavioral Tracking Engine (Analytics)
 * This script monitors user dwell time and clicks to build an "Interest Profile".
 */

const AnalyticsEngine = {
    data: {
        sectionsViewed: {}, // { "services": 45, "portfolio": 120 } (in seconds)
        clicks: {}, // { "Read Article: Personal Branding": 1 }
        consentGiven: false
    },
    
    // Internal state for tracking active sections
    activeSections: {},

    init: function() {
        // Load existing data if consent was previously given
        const savedData = localStorage.getItem('caraway_analytics_profile');
        const consent = localStorage.getItem('caraway_cookie_consent');
        
        if (consent === 'true') {
            this.data.consentGiven = true;
            this.hideCookieBanner();
            if (savedData) {
                try {
                    const parsed = JSON.parse(savedData);
                    this.data.sectionsViewed = parsed.sectionsViewed || {};
                    this.data.clicks = parsed.clicks || {};
                } catch (e) {
                    console.error("Failed to parse analytics data");
                }
            }
        } else if (consent === 'false') {
            this.hideCookieBanner();
        }

        this.setupCookieBanner();
        this.setupIntersectionObserver();
        this.setupClickTracking();
        
        // Expose function globally for the contact form
        window.getVisitorProfile = this.getVisitorProfile.bind(this);
    },

    saveData: function() {
        if (this.data.consentGiven) {
            localStorage.setItem('caraway_analytics_profile', JSON.stringify({
                sectionsViewed: this.data.sectionsViewed,
                clicks: this.data.clicks
            }));
        }
    },

    setupCookieBanner: function() {
        const acceptBtn = document.getElementById('accept-cookies');
        const declineBtn = document.getElementById('decline-cookies');
        
        if (acceptBtn) {
            acceptBtn.addEventListener('click', () => {
                this.data.consentGiven = true;
                localStorage.setItem('caraway_cookie_consent', 'true');
                this.hideCookieBanner();
                this.saveData();
            });
        }
        if (declineBtn) {
            declineBtn.addEventListener('click', () => {
                this.data.consentGiven = false;
                localStorage.setItem('caraway_cookie_consent', 'false');
                this.hideCookieBanner();
            });
        }
    },

    hideCookieBanner: function() {
        const banner = document.getElementById('cookie-banner');
        if (banner) {
            banner.style.display = 'none';
        }
    },

    setupIntersectionObserver: function() {
        const sections = document.querySelectorAll('section[id]');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const id = entry.target.getAttribute('id');
                if (entry.isIntersecting) {
                    // Start timer
                    this.activeSections[id] = Date.now();
                } else {
                    // Stop timer and add to total
                    if (this.activeSections[id]) {
                        const timeSpent = (Date.now() - this.activeSections[id]) / 1000; // in seconds
                        if (!this.data.sectionsViewed[id]) {
                            this.data.sectionsViewed[id] = 0;
                        }
                        this.data.sectionsViewed[id] += timeSpent;
                        delete this.activeSections[id];
                        this.saveData();
                    }
                }
            });
        }, { threshold: 0.5 }); // Tracks when 50% of the section is visible

        sections.forEach(section => observer.observe(section));
        
        // Ensure we save time on page unload
        window.addEventListener('beforeunload', () => {
            Object.keys(this.activeSections).forEach(id => {
                const timeSpent = (Date.now() - this.activeSections[id]) / 1000;
                if (!this.data.sectionsViewed[id]) {
                    this.data.sectionsViewed[id] = 0;
                }
                this.data.sectionsViewed[id] += timeSpent;
            });
            this.saveData();
        });
    },

    setupClickTracking: function() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn');
            if (btn) {
                // Ignore the generic contact buttons
                const btnText = btn.innerText.trim();
                const parentSection = btn.closest('section');
                const sectionId = parentSection ? parentSection.getAttribute('id') : 'unknown';
                
                const clickId = `${sectionId} -> ${btnText}`;
                
                if (!this.data.clicks[clickId]) {
                    this.data.clicks[clickId] = 0;
                }
                this.data.clicks[clickId]++;
                this.saveData();
            }
        });
    },

    getVisitorProfile: function() {
        if (!this.data.consentGiven) {
            return "Analytics Disabled (No Consent)";
        }
        
        // Find top section by time
        let topSection = "None";
        let maxTime = 0;
        
        for (const [section, time] of Object.entries(this.data.sectionsViewed)) {
            if (time > maxTime) {
                maxTime = time;
                topSection = section;
            }
        }
        
        // Format report
        let report = `--- Visitor Analytics Profile ---\n`;
        report += `Top Section Viewed: ${topSection} (${Math.round(maxTime)} seconds)\n`;
        report += `Total Sections Viewed Data: ${JSON.stringify(this.data.sectionsViewed)}\n`;
        report += `Key Buttons Clicked: ${JSON.stringify(this.data.clicks)}\n`;
        
        return report;
    }
};

// Initialize the engine when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    AnalyticsEngine.init();
});
