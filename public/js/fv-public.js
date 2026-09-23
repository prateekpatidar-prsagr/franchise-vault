/* jshint esversion: 6 */
(function ($) {
  'use strict';

  /* ============================================================
     FranchiseVault — Public JS
     Handles: Security, Watermark SVG, Filters, Sort, Search
  ============================================================ */

  var FV = {

    data:    window.fvData || {},
    $portal: null,
    $grid:   null,
    $cards:  null,

    activeType: 'all',
    activeCat:  'all',
    sortOrder:  'newest',
    searchTerm: '',

    // ── Init ────────────────────────────────────────────────────
    init: function () {
      this.$portal = $('#fv-portal, #fv-module-view');
      this.$grid   = $('#fv-grid');
      this.$cards  = this.$grid.find('.fv-card');

      this.applyBrand();
      this.initSecurity();
      this.buildWatermark();
      this.initFilters();
      this.initSearch();
      this.initGalleryLightbox();
    },

    // ── Brand color ─────────────────────────────────────────────
    applyBrand: function () {
      if (this.data.brand) {
        document.documentElement.style.setProperty('--fv-brand', this.data.brand);
      }
      if (this.data.accent) {
        document.documentElement.style.setProperty('--fv-accent2', this.data.accent);
      }
    },

    // ── Security ────────────────────────────────────────────────
    initSecurity: function () {
      var self = this;

      // ── Right-click ──────────────────────────────────────────
      document.addEventListener('contextmenu', function (e) {
        if ($(e.target).closest('.fv-wrap').length) {
          e.preventDefault();
          self.flashWarning('Right-click is disabled on this portal.');
        }
      });

      // ── Keyboard shortcuts ───────────────────────────────────
      document.addEventListener('keydown', function (e) {
        if (!$(document.activeElement).closest('.fv-wrap, #fv-portal, #fv-module-view').length &&
            !$(e.target).closest('.fv-wrap').length) return;

        var ctrl = e.ctrlKey || e.metaKey;
        var k    = e.key ? e.key.toLowerCase() : '';

        if (ctrl && (k === 's' || k === 'u' || k === 'p' || k === 'a')) {
          e.preventDefault();
          return false;
        }
        if (k === 'f12') {
          e.preventDefault();
          return false;
        }
        if (e.key === 'PrintScreen') {
          e.preventDefault();
        }
      });

      // ── Drag prevention ──────────────────────────────────────
      document.addEventListener('dragstart', function (e) {
        if ($(e.target).closest('.fv-wrap').length) {
          e.preventDefault();
        }
      });

      // ── Select prevention on media ───────────────────────────
      document.addEventListener('selectstart', function (e) {
        if ($(e.target).closest('.fv-media-wrap, .fv-card-visual').length) {
          e.preventDefault();
        }
      });

      // ── DevTools detection ───────────────────────────────────
      var devOpen   = false;
      var threshold = 150;

      function checkDevTools() {
        var widthDiff  = window.outerWidth  - window.innerWidth;
        var heightDiff = window.outerHeight - window.innerHeight;

        if (widthDiff > threshold || heightDiff > threshold) {
          if (!devOpen) {
            devOpen = true;
            self.blurMedia(true);
            self.showDevToolsWarning(true);
          }
        } else {
          if (devOpen) {
            devOpen = false;
            self.blurMedia(false);
            self.showDevToolsWarning(false);
          }
        }
      }

      // Check every 1.5 seconds
      setInterval(checkDevTools, 1500);
    },

    blurMedia: function (on) {
      var $m = $('.fv-media-wrap, .fv-video-ratio, .fv-doc-wrap, .fv-gallery');
      $m.css('filter', on ? 'blur(22px)' : '');
    },

    showDevToolsWarning: function (on) {
      var $el = $('#fv-dt-warn');
      if (on) {
        if (!$el.length) {
          $('<div id="fv-dt-warn">🔒 Close Developer Tools to continue viewing content.</div>')
            .css({
              position:    'fixed',
              top:         '50%',
              left:        '50%',
              transform:   'translate(-50%,-50%)',
              background:  'rgba(13,17,32,0.98)',
              color:       '#eef0f8',
              padding:     '24px 48px',
              borderRadius: '18px',
              zIndex:      999999,
              fontFamily:  'Inter,sans-serif',
              fontSize:    '16px',
              fontWeight:  600,
              textAlign:   'center',
              border:      '1px solid rgba(108,99,255,0.5)',
              boxShadow:   '0 8px 40px rgba(0,0,0,0.8)',
              whiteSpace:  'nowrap'
            })
            .appendTo('body');
        }
      } else {
        $el.remove();
      }
    },

    flashWarning: function (msg) {
      var $el = $('<div class="fv-flash-warn">' + msg + '</div>').css({
        position:    'fixed',
        bottom:      '28px',
        left:        '50%',
        transform:   'translateX(-50%) translateY(10px)',
        background:  'rgba(108,99,255,0.95)',
        color:       '#fff',
        padding:     '12px 28px',
        borderRadius: '12px',
        zIndex:      99999,
        fontFamily:  'Inter,sans-serif',
        fontSize:    '14px',
        fontWeight:  600,
        opacity:     0,
        transition:  'all 0.3s'
      });
      $el.appendTo('body');
      setTimeout(function () { $el.css({ opacity: 1, transform: 'translateX(-50%) translateY(0)' }); }, 10);
      setTimeout(function () { $el.css({ opacity: 0, transform: 'translateX(-50%) translateY(10px)' }); }, 2500);
      setTimeout(function () { $el.remove(); }, 3000);
    },

    // ── Watermark ────────────────────────────────────────────────
    buildWatermark: function () {
      var $wm = $('#fv-wm');
      if (!$wm.length) return;

      var text = (this.data.watermark || 'CONFIDENTIAL').toUpperCase();

      // Build SVG tiled watermark
      var svgNS = 'http://www.w3.org/2000/svg';

      var svg     = document.createElementNS(svgNS, 'svg');
      var defs    = document.createElementNS(svgNS, 'defs');
      var pattern = document.createElementNS(svgNS, 'pattern');
      var textEl  = document.createElementNS(svgNS, 'text');
      var rect    = document.createElementNS(svgNS, 'rect');

      // Pattern setup
      pattern.setAttribute('id', 'fv-wm-pat');
      pattern.setAttribute('x', '0');
      pattern.setAttribute('y', '0');
      pattern.setAttribute('width', '320');
      pattern.setAttribute('height', '110');
      pattern.setAttribute('patternUnits', 'userSpaceOnUse');
      pattern.setAttribute('patternTransform', 'rotate(-28)');

      // Text node
      textEl.setAttribute('x', '10');
      textEl.setAttribute('y', '60');
      textEl.setAttribute('fill', 'rgba(255,255,255,0.055)');
      textEl.setAttribute('font-size', '13');
      textEl.setAttribute('font-family', 'Inter, Arial, sans-serif');
      textEl.setAttribute('font-weight', '700');
      textEl.setAttribute('letter-spacing', '3');
      textEl.textContent = text;

      // Rect fills pattern
      rect.setAttribute('width',  '100%');
      rect.setAttribute('height', '100%');
      rect.setAttribute('fill',   'url(#fv-wm-pat)');

      pattern.appendChild(textEl);
      defs.appendChild(pattern);
      svg.appendChild(defs);
      svg.appendChild(rect);

      svg.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;';
      $wm[0].appendChild(svg);
    },

    // ── Filters & Sort ───────────────────────────────────────────
    initFilters: function () {
      if (!this.$grid.length) return;
      var self = this;

      // Type pills
      $(document).on('click', '[data-fv-type]', function () {
        self.activeType = $(this).data('fv-type');
        $('#fv-type-pills .fv-pill').removeClass('fv-pill-active');
        $(this).addClass('fv-pill-active');
        self.applyFilters();
      });

      // Cat pills
      $(document).on('click', '[data-fv-cat]', function () {
        self.activeCat = $(this).data('fv-cat');
        $('#fv-cat-pills .fv-pill').removeClass('fv-pill-active');
        $(this).addClass('fv-pill-active');
        self.applyFilters();
      });

      // Sort
      $('#fv-sort').on('change', function () {
        self.sortOrder = $(this).val();
        self.applyFilters();
      });

      // Reset button
      $('#fv-reset-filters').on('click', function () {
        self.reset();
      });
    },

    initSearch: function () {
      var self = this;
      var timeout;

      $('#fv-search').on('input', function () {
        clearTimeout(timeout);
        var val = $(this).val().trim();
        $('#fv-search-clear').toggle(val.length > 0);
        timeout = setTimeout(function () {
          self.searchTerm = val.toLowerCase();
          self.applyFilters();
        }, 220);
      });

      $('#fv-search-clear').on('click', function () {
        $('#fv-search').val('').trigger('input');
      });
    },

    applyFilters: function () {
      var self    = this;
      var visible = 0;

      this.$cards.each(function () {
        var $c    = $(this);
        var type  = $c.data('type')  || '';
        var cats  = ($c.data('cat') || '').split(',');
        var title = $c.data('title') || '';

        var typeOk   = (self.activeType === 'all' || type === self.activeType);
        var catOk    = (self.activeCat  === 'all' || cats.indexOf(self.activeCat) !== -1);
        var searchOk = (!self.searchTerm || title.indexOf(self.searchTerm) !== -1);

        if (typeOk && catOk && searchOk) {
          $c.removeClass('fv--hide');
          visible++;
        } else {
          $c.addClass('fv--hide');
        }
      });

      // Sort visible cards
      this.sortCards();

      // Update count
      $('#fv-count').text(visible);

      // Toggle no-results
      var $noRes = $('#fv-no-results');
      if ($noRes.length) {
        if (visible === 0) {
          $noRes.css('display', 'block');
        } else {
          $noRes.css('display', 'none');
        }
      }
    },

    sortCards: function () {
      var self    = this;
      var $grid   = this.$grid;
      var visible = this.$cards.filter(':not(.fv--hide)').toArray();

      visible.sort(function (a, b) {
        var ta = $(a).data('title') || '';
        var tb = $(b).data('title') || '';
        var da = parseInt($(a).data('date') || 0, 10);
        var db = parseInt($(b).data('date') || 0, 10);

        switch (self.sortOrder) {
          case 'newest': return db - da;
          case 'oldest': return da - db;
          case 'az':     return ta.localeCompare(tb);
          case 'za':     return tb.localeCompare(ta);
          default:       return 0;
        }
      });

      visible.forEach(function (el) { $grid.append(el); });
    },

    reset: function () {
      this.activeType = 'all';
      this.activeCat  = 'all';
      this.searchTerm = '';

      $('#fv-search').val('');
      $('#fv-search-clear').hide();
      $('#fv-type-pills .fv-pill, #fv-cat-pills .fv-pill').removeClass('fv-pill-active');
      $('#fv-type-pills .fv-pill[data-fv-type="all"]').addClass('fv-pill-active');
      $('#fv-cat-pills  .fv-pill[data-fv-cat="all"]').addClass('fv-pill-active');

      this.applyFilters();
    },

    // ── Simple Gallery Lightbox ──────────────────────────────────
    initGalleryLightbox: function () {
      if (!$('.fv-gallery').length) return;

      $(document).on('click', '.fv-gal-img', function () {
        var src   = $(this).attr('src');
        var $overlay = $('<div id="fv-lb" style="' +
          'position:fixed;inset:0;background:rgba(0,0,0,0.95);z-index:99999;' +
          'display:flex;align-items:center;justify-content:center;cursor:zoom-out;' +
          'animation:fvFadeIn 0.2s ease;">' +
          '<img src="' + src + '" style="max-width:90vw;max-height:88vh;border-radius:12px;' +
          'box-shadow:0 24px 80px rgba(0,0,0,0.8);" />' +
          '<button style="position:absolute;top:20px;right:24px;background:rgba(255,255,255,0.1);' +
          'border:none;color:#fff;font-size:26px;cursor:pointer;border-radius:8px;' +
          'width:44px;height:44px;line-height:1;" id="fv-lb-close">✕</button>' +
          '</div>');

        $overlay.appendTo('body');

        $overlay.on('click', function (e) {
          if ($(e.target).is('#fv-lb') || $(e.target).is('#fv-lb-close')) {
            $overlay.fadeOut(200, function () { $overlay.remove(); });
          }
        });

        $(document).on('keydown.fvlb', function (e) {
          if (e.key === 'Escape') {
            $overlay.fadeOut(200, function () { $overlay.remove(); });
            $(document).off('keydown.fvlb');
          }
        });
      });
    }

  };

  // ── DOM Ready ───────────────────────────────────────────────────
  $(document).ready(function () {
    if ($('#fv-portal, #fv-module-view').length) {
      FV.init();
    }
  });

})(jQuery);
