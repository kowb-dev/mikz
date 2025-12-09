// Ensure the DOM is fully loaded before executing scripts
document.addEventListener('DOMContentLoaded', function() {
    const secondaryWrapper = document.querySelector('.secondary-wrapper');
    const closeFilterButton = document.querySelector('.filters.close-drawer');
    // Assuming there's a button to open the filters, let's select it
    // For now, we'll assume a button with class 'open-filters-button'
    const openFilterButton = document.querySelector('.mobile-filter'); 

    // Function to open the filter drawer
    function openFilterDrawer() {
        if (secondaryWrapper) {
            secondaryWrapper.classList.add('open');
            document.body.style.overflow = 'hidden'; // Prevent scrolling when drawer is open
        }
    }

    // Function to close the filter drawer
    function closeFilterDrawer() {
        if (secondaryWrapper) {
            secondaryWrapper.classList.remove('open');
            document.body.style.overflow = ''; // Restore scrolling
        }
    }

    // Event listener for opening the filter drawer
    if (openFilterButton) {
        openFilterButton.addEventListener('click', openFilterDrawer);
    }

    // Event listener for closing the filter drawer
    if (closeFilterButton) {
        closeFilterButton.addEventListener('click', closeFilterDrawer);
    }

    // Close drawer when clicking outside the widget-area
    if (secondaryWrapper) {
        secondaryWrapper.addEventListener('click', function(event) {
            if (event.target === secondaryWrapper) {
                closeFilterDrawer();
            }
        });
    }
});
