/* jshint esversion: 6 */
(function ($) {
  'use strict';

  /* ============================================================
     FranchiseVault Admin JS
     — Conditional field show/hide based on content type
     — WordPress media library picker
     — Color input live preview
  ============================================================ */

  function updateFields() {
    var selected = $('input[name="fv_type"]:checked').val() || 'video';

    $('.fv-cond').each(function () {
      var types = ($(this).data('types') || '').split(' ');
      if (types.indexOf(selected) !== -1) {
        $(this).slideDown(220);
      } else {
        $(this).slideUp(180);
      }
    });

    // Highlight active tile
    $('.fv-type-tile').removeClass('is-active');
    $('input[name="fv_type"][value="' + selected + '"]')
      .closest('.fv-type-tile')
      .addClass('is-active');
  }

  $(document).ready(function () {

    // ── Initial state ──────────────────────────────────────
    updateFields();

    // ── Radio change ───────────────────────────────────────
    $(document).on('change', 'input[name="fv_type"]', function () {
      updateFields();
    });

    // ── Tile click ─────────────────────────────────────────
    $(document).on('click', '.fv-type-tile', function () {
      $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
    });

    // ── Media Library picker ───────────────────────────────
    var mediaFrame;

    $('#fv_pick_thumb').on('click', function (e) {
      e.preventDefault();
      if (mediaFrame) {
        mediaFrame.open();
        return;
      }

      mediaFrame = wp.media({
        title:    'Choose Card Thumbnail',
        button:   { text: 'Use this image' },
        multiple: false,
        library:  { type: 'image' }
      });

      mediaFrame.on('select', function () {
        var att = mediaFrame.state().get('selection').first().toJSON();
        $('#fv_thumb_url').val(att.url);

        var $prev = $('#fv-thumb-preview');
        $prev.attr('src', att.url).show();
      });

      mediaFrame.open();
    });

    // ── Color value live label ─────────────────────────────
    $('input[type="color"]').on('input change', function () {
      $(this).siblings('.fv-color-val').text($(this).val());
    });

  });

})(jQuery);
