"use strict";

// ─── Drag-and-drop sort for the advertisement index table ─────────────────────
$(document).ready(function () {

    var $tbody = $('#advertisement-sortable');
    var sortUrl = $tbody.data('url');

    if ($tbody.length && sortUrl) {
        $tbody.sortable({
            handle    : '.sort-handler',
            axis      : 'y',
            cursor    : 'grabbing',
            opacity   : 0.7,
            update    : function () {
                var ids = $tbody.sortable('toArray', { attribute: 'data-id' }).join(',');

                $.ajax({
                    url     : sortUrl,
                    method  : 'POST',
                    data    : {
                        ids   : ids,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success : function (res) {
                        if (res && res.success) {
                            showToast('Sort order updated.', 'success');
                        }
                    },
                    error   : function () {
                        showToast('Failed to save sort order.', 'error');
                    }
                });
            }
        });
    }

    // ─── Tiny toast helper ────────────────────────────────────────────────────
    function showToast(message, type) {
        var bg    = type === 'success' ? '#16a34a' : '#dc2626';
        var toast = $('<div>')
            .text(message)
            .css({
                position      : 'fixed',
                bottom        : '24px',
                right         : '24px',
                background    : bg,
                color         : '#fff',
                padding       : '10px 18px',
                borderRadius  : '6px',
                fontSize      : '13px',
                zIndex        : 9999,
                boxShadow     : '0 4px 12px rgba(0,0,0,.15)',
                opacity       : 0,
                transition    : 'opacity .3s ease'
            });

        $('body').append(toast);
        setTimeout(function () { toast.css('opacity', 1); }, 10);
        setTimeout(function () {
            toast.css('opacity', 0);
            setTimeout(function () { toast.remove(); }, 350);
        }, 2500);
    }
});
