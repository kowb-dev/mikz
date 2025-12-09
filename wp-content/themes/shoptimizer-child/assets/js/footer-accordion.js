/**
 * Footer Accordion Script (Corrected)
 *
 * @package shoptimizer-child
 * @version 1.1.0
 */
document.addEventListener('DOMContentLoaded', function () {
    // Select only the titles within the specified accordion sections
    const accordionTitles = document.querySelectorAll('.mkx-footer-additional .mkx-footer-section-title, .mkx-footer-policies .mkx-footer-section-title');
    const mobileMaxWidth = 768;

    if (accordionTitles.length === 0) {
        return;
    }

    accordionTitles.forEach(title => {
        title.addEventListener('click', function(event) {
            // Only run the accordion logic on mobile screens
            if (window.innerWidth <= mobileMaxWidth) {
                event.preventDefault();

                // The parent is the section div (.mkx-footer-additional or .mkx-footer-policies)
                const parentSection = this.parentElement; 
                parentSection.classList.toggle('active');
            }
        });
    });
});
