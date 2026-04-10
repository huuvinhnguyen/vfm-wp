/**
 * VietFarmy Slideshow - Frontend JavaScript
 * Handles fade/slide transitions, autoplay, touch swipe, keyboard
 */
(function() {
    'use strict';

    function init() {
        document.querySelectorAll('.vsw-container').forEach(function(container) {
            new VietFarmiSlideshow(container);
        });
    }

    function VietFarmiSlideshow(container) {
        this.container = container;
        this.uid = container.id;
        this.slides = [];
        this.current = 0;
        this.total = 0;
        this.autoplay = false;
        this.speed = 4000;
        this.transition = 'fade';
        this.timer = null;
        this.progressTimer = null;
        this.isPaused = false;
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.isDragging = false;

        try {
            this.slides = JSON.parse(container.dataset.slides || '[]');
        } catch (e) {
            return;
        }

        if (this.slides.length === 0) return;

        this.autoplay = parseInt(container.dataset.autoplay || 1) === 1;
        this.speed = parseInt(container.dataset.speed || 4000);
        this.transition = container.dataset.transition || 'fade';
        this.height = parseInt(container.dataset.height || 480);
        this.showNav = parseInt(container.dataset.nav || 1) === 1;
        this.showDots = parseInt(container.dataset.dots || 1) === 1;
        this.showCaption = parseInt(container.dataset.caption || 1) === 1;
        this.total = this.slides.length;

        // Set container height
        container.style.maxHeight = this.height + 'px';

        this.track = container.querySelector('.vsw-track');
        if (!this.track) return;

        // Build slides
        this.buildSlides();

        // Build dots
        if (this.showDots) {
            this.buildDots();
        }

        // Navigation
        if (this.showNav) {
            this.bindNav();
        }

        // Caption
        if (this.showCaption) {
            this.buildCaption();
        }

        // Autoplay
        if (this.autoplay) {
            this.bindPlayPause();
            this.startAutoplay();
        }

        // Touch / Swipe
        this.bindTouch();

        // Keyboard
        this.bindKeyboard();

        // Click slide to open link
        this.bindClick();

        // Lazy load non-first slides
        this.lazyLoad();
    }

    VietFarmiSlideshow.prototype.buildSlides = function() {
        var self = this;
        this.track.innerHTML = '';

        if (this.transition === 'slide') {
            this.track.style.display = 'flex';
            this.track.style.flexWrap = 'nowrap';
        }

        this.slides.forEach(function(s, i) {
            var slide = document.createElement('div');
            slide.className = 'vsw-slide' + (i === 0 ? ' active' : '');

            var content = '';
            if (s.link) {
                content += '<a href="' + s.link + '"' +
                    (s.newtab ? ' target="_blank" rel="noopener noreferrer"' : '') +
                    ' tabindex="' + (i === 0 ? '0' : '-1') + '">';
            }
            content += '<img src="' + s.src + '" alt="' + (s.alt || '') + '" loading="' + (i === 0 ? 'eager' : 'lazy') + '">';
            if (s.link) {
                content += '</a>';
            }

            slide.innerHTML = content;
            self.track.appendChild(slide);
        });
    };

    VietFarmiSlideshow.prototype.buildDots = function() {
        var self = this;
        var dotsEl = this.container.querySelector('.vsw-dots');
        if (!dotsEl) return;

        dotsEl.innerHTML = '';
        this.slides.forEach(function(_, i) {
            var btn = document.createElement('button');
            btn.className = 'vsw-dot' + (i === 0 ? ' active' : '');
            btn.setAttribute('aria-label', 'Slide ' + (i + 1));
            btn.addEventListener('click', function() {
                self.goTo(i);
            });
            dotsEl.appendChild(btn);
        });
    };

    VietFarmiSlideshow.prototype.buildCaption = function() {
        var capEl = this.container.querySelector('.vsw-caption');
        if (!capEl) return;
        capEl.innerHTML = '';
    };

    VietFarmiSlideshow.prototype.goTo = function(idx, userAction) {
        var self = this;
        var next = ((idx % this.total) + this.total) % this.total;
        if (next === this.current && !userAction) return;

        var slides = this.track.querySelectorAll('.vsw-slide');

        if (this.transition === 'fade') {
            slides.forEach(function(s) { s.classList.remove('active'); });
            slides[next].classList.add('active');
        } else {
            this.track.style.transform = 'translateX(-' + (next * 100) + '%)';
        }

        // Update dots
        if (this.showDots) {
            var dots = this.container.querySelectorAll('.vsw-dot');
            dots.forEach(function(d, i) {
                d.classList.toggle('active', i === next);
            });
        }

        // Update caption
        if (this.showCaption) {
            var capEl = this.container.querySelector('.vsw-caption');
            if (capEl) {
                var s = this.slides[next];
                if (s.title || s.desc) {
                    capEl.innerHTML =
                        (s.title ? '<p class="vsw-caption-title">' + s.title + '</p>' : '') +
                        (s.desc  ? '<p class="vsw-caption-desc">'  + s.desc  + '</p>' : '');
                } else {
                    capEl.innerHTML = '';
                }
            }
        }

        this.current = next;

        // Reset autoplay timer
        if (this.autoplay && !this.isPaused) {
            this.restartAutoplay();
        }
    };

    VietFarmiSlideshow.prototype.next = function() {
        this.goTo(this.current + 1, true);
    };

    VietFarmiSlideshow.prototype.prev = function() {
        this.goTo(this.current - 1, true);
    };

    VietFarmiSlideshow.prototype.startAutoplay = function() {
        var self = this;
        this.timer = setInterval(function() {
            if (!self.isPaused) self.goTo(self.current + 1);
        }, this.speed);
    };

    VietFarmiSlideshow.prototype.restartAutoplay = function() {
        clearInterval(this.timer);
        this.startAutoplay();
    };

    VietFarmiSlideshow.prototype.pause = function() {
        this.isPaused = true;
        clearInterval(this.timer);
        this.container.classList.add('paused');
        this.updatePlayPauseIcon();
    };

    VietFarmiSlideshow.prototype.resume = function() {
        this.isPaused = false;
        clearInterval(this.timer);
        this.startAutoplay();
        this.container.classList.remove('paused');
        this.updatePlayPauseIcon();
    };

    VietFarmiSlideshow.prototype.toggle = function() {
        if (this.isPaused) {
            this.resume();
        } else {
            this.pause();
        }
    };

    VietFarmiSlideshow.prototype.updatePlayPauseIcon = function() {
        var btn = this.container.querySelector('.vsw-playpause');
        if (!btn) return;
        var iconPause = btn.querySelector('.icon-pause');
        var iconPlay  = btn.querySelector('.icon-play');
        if (iconPause) iconPause.style.display = this.isPaused ? 'none' : '';
        if (iconPlay)  iconPlay.style.display  = this.isPaused ? '' : 'none';
    };

    VietFarmiSlideshow.prototype.bindNav = function() {
        var self = this;
        var prevBtn = this.container.querySelector('.vsw-btn-prev');
        var nextBtn = this.container.querySelector('.vsw-btn-next');

        if (prevBtn) prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            self.prev();
        });

        if (nextBtn) nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            self.next();
        });
    };

    VietFarmiSlideshow.prototype.bindPlayPause = function() {
        var btn = this.container.querySelector('.vsw-playpause');
        if (btn) {
            var self = this;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                self.toggle();
            });
        }
        // Pause on hover
        this.container.addEventListener('mouseenter', function() {
            if (self.autoplay && !self.isPaused) {
                clearInterval(self.timer);
            }
        });
        this.container.addEventListener('mouseleave', function() {
            if (self.autoplay && !self.isPaused) {
                self.startAutoplay();
            }
        });
    };

    VietFarmiSlideshow.prototype.bindTouch = function() {
        var self = this;
        this.container.addEventListener('touchstart', function(e) {
            // Only track horizontal swipes
            if (e.touches.length === 1) {
                self.touchStartX = e.touches[0].clientX;
                self.touchStartY = e.touches[0].clientY;
            }
        }, { passive: true });

        this.container.addEventListener('touchend', function(e) {
            var dx = e.changedTouches[0].clientX - self.touchStartX;
            var dy = e.changedTouches[0].clientY - self.touchStartY;
            // Only swipe if horizontal movement is greater
            if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 40) {
                if (dx < 0) self.next();
                else self.prev();
            }
        }, { passive: true });
    };

    VietFarmiSlideshow.prototype.bindKeyboard = function() {
        var self = this;
        this.container.setAttribute('tabindex', '0');
        this.container.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft')  { e.preventDefault(); self.prev(); }
            if (e.key === 'ArrowRight') { e.preventDefault(); self.next(); }
            if (e.key === ' ')          { e.preventDefault(); self.toggle(); }
        });
    };

    VietFarmiSlideshow.prototype.bindClick = function() {
        var self = this;
        this.track.querySelectorAll('.vsw-slide').forEach(function(slide, i) {
            var link = slide.querySelector('a');
            if (link) {
                // Already linked, no action needed
            }
        });
    };

    VietFarmiSlideshow.prototype.lazyLoad = function() {
        // Native loading="lazy" handles this, but we can preload next slide
        // Using IntersectionObserver for more control
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target.querySelector('img[loading="lazy"]');
                        if (img) {
                            img.setAttribute('loading', 'eager');
                        }
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0 });
            observer.observe(this.container);
        }
    };

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
