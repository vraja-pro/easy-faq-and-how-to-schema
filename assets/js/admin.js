/**
 * Admin JavaScript for Easy FAQ and HowTo Schema
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        
        // FAQ functionality
        var faqIndex = $('.faq-item').length;

        // Add new FAQ item
        $('.add-faq-item').on('click', function() {
            var template = $('#faq-item-template').html();
            template = template.replace(/\{\{INDEX\}\}/g, faqIndex);
            $('.faq-items-container').append(template);
            updateFaqNumbers();
            faqIndex++;
        });

        // Remove FAQ item
        $(document).on('click', '.faq-item .remove-item', function() {
            if (confirm(easyFaqHowtoAdmin.confirmDelete)) {
                $(this).closest('.faq-item').remove();
                updateFaqNumbers();
            }
        });

        // Update FAQ item numbers
        function updateFaqNumbers() {
            $('.faq-item').each(function(index) {
                $(this).find('.faq-item-number').text(index + 1);
            });
        }

        // HowTo functionality
        var howtoIndex = $('.howto-step').length;

        // Add new HowTo step
        $('.add-howto-step').on('click', function() {
            var template = $('#howto-step-template').html();
            template = template.replace(/\{\{INDEX\}\}/g, howtoIndex);
            $('.howto-steps-container').append(template);
            updateHowtoNumbers();
            howtoIndex++;
        });

        // Remove HowTo step
        $(document).on('click', '.howto-step .remove-item', function() {
            if (confirm(easyFaqHowtoAdmin.confirmDelete)) {
                $(this).closest('.howto-step').remove();
                updateHowtoNumbers();
            }
        });

        // Update HowTo step numbers
        function updateHowtoNumbers() {
            $('.howto-step').each(function(index) {
                $(this).find('.howto-step-number').text(index + 1);
            });
        }

        // Make items sortable (optional enhancement)
        if ($.fn.sortable) {
            $('.faq-items-container').sortable({
                items: '.faq-item',
                handle: '.faq-item-header',
                placeholder: 'sortable-placeholder',
                update: function() {
                    updateFaqNumbers();
                }
            });

            $('.howto-steps-container').sortable({
                items: '.howto-step',
                handle: '.howto-step-header',
                placeholder: 'sortable-placeholder',
                update: function() {
                    updateHowtoNumbers();
                }
            });
        }
    });

})(jQuery);
