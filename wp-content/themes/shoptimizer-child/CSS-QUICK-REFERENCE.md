# CSS Quick Reference - Shoptimizer Child

## 📁 Where to Edit What

### Need to change colors, spacing, fonts?
→ `assets/css/01-variables.css`

### Need to fix shop page layout (desktop)?
→ `assets/css/05-woocommerce-list.css`

### Need to fix shop page layout (mobile)?
→ `assets/css/08-responsive-mobile.css`

### Need to change product grid (category pages)?
→ `assets/css/04-woocommerce-grid.css`

### Need to change header?
→ `assets/css/header.css`

### Need to change footer?
→ `assets/css/footer.css`

### Need to change mobile navigation?
→ `assets/css/mobile-nav.css`

### Need to add global container/layout?
→ `assets/css/02-layout.css`

### Need to add utility class?
→ `assets/css/03-utilities.css`

### Need to change single product page?
→ `style.css` (lines 43-383)

### Need to change WooCommerce notices/cart?
→ `assets/css/06-woocommerce-base.css`

## 📱 Responsive Breakpoints

```css
/* Mobile First - No media query needed */
/* Default styles apply to 375px+ */

/* Tablet */
@media (min-width: 768px) { }

/* Desktop */
@media (min-width: 992px) { }

/* Large Desktop */
@media (min-width: 1200px) { }

/* Extra Large */
@media (min-width: 1400px) { }
```

## 🎨 CSS Variable Usage

```css
/* Colors */
var(--mkx-primary)        /* #F57300 - Orange */
var(--mkx-white)          /* #FFFFFF */
var(--mkx-black)          /* #0F172A */
var(--mkx-success)        /* #059669 - Green */
var(--mkx-error)          /* #DC2626 - Red */

/* Spacing */
var(--space-xs)           /* clamp(0.25rem, 0.5vw, 0.5rem) */
var(--space-sm)           /* clamp(0.5rem, 1vw, 0.75rem) */
var(--space-md)           /* clamp(0.75rem, 1.5vw, 1rem) */
var(--space-lg)           /* clamp(1rem, 2vw, 1.5rem) */
var(--space-xl)           /* clamp(1.5rem, 3vw, 2rem) */

/* Font Sizes */
var(--fs-xs)              /* clamp(0.75rem, 1.5vw, 0.875rem) */
var(--fs-sm)              /* clamp(0.875rem, 2vw, 1rem) */
var(--fs-base)            /* clamp(1rem, 2.5vw, 1.125rem) */
var(--fs-lg)              /* clamp(1.125rem, 3vw, 1.25rem) */
var(--fs-xl)              /* clamp(1.25rem, 3.5vw, 1.5rem) */

/* Border Radius */
var(--radius-sm)          /* clamp(0.25rem, 0.5vw, 0.5rem) */
var(--radius-md)          /* clamp(0.375rem, 0.75vw, 0.75rem) */
var(--radius-lg)          /* clamp(0.5rem, 1vw, 1rem) */
var(--radius-xl)          /* clamp(0.75rem, 1.5vw, 1.5rem) */
var(--radius-full)        /* 9999px */

/* Shadows */
var(--shadow-sm)
var(--shadow-md)
var(--shadow-lg)
var(--shadow-xl)

/* Transitions */
var(--mkx-transition-fast)    /* 0.15s */
var(--mkx-transition-medium)  /* 0.3s */
var(--mkx-ease)               /* cubic-bezier(0.4, 0, 0.2, 1) */
```

## 🛒 WooCommerce Page Selectors

### Shop Page (Main Product Archive)
```css
.woocommerce.post-type-archive-product { }
.woocommerce.post-type-archive-product ul.products { }
.woocommerce.post-type-archive-product li.product { }
```

### Category Page
```css
.tax-product_cat { }
.tax-product_cat ul.products { }
.tax-product_cat li.product { }
```

### Brand Page
```css
.tax-pwb-brand { }
.tax-pwb-brand ul.products { }
.tax-pwb-brand li.product { }
```

### Home Page Products
```css
.home .woocommerce ul.products { }
.home .woocommerce li.product { }
```

### Single Product Page
```css
.single-product div.product { }
.single-product .summary { }
.single-product form.cart { }
```

### Cart Page
```css
.woocommerce-cart { }
.woocommerce-cart table.cart { }
```

### Checkout Page
```css
.woocommerce-checkout { }
```

## 🔧 Common Tasks

### Add New CSS Variable
1. Open `assets/css/01-variables.css`
2. Add in appropriate section within `:root { }`
3. Use in other files: `var(--your-variable-name)`

### Fix Mobile Issue
1. Open `assets/css/08-responsive-mobile.css`
2. Add styles within appropriate `@media` query
3. Use mobile-first approach (override desktop styles)

### Add New Component
1. Create new file: `assets/css/10-component-name.css`
2. Open `inc/core/enqueue.php`
3. Add enqueue with proper dependencies:
```php
wp_enqueue_style(
    'mkx-component-name',
    get_stylesheet_directory_uri() . '/assets/css/10-component-name.css',
    array( 'mkx-variables', 'mkx-layout' ),
    $version
);
```

### Debug CSS Conflicts
1. Use browser DevTools
2. Check file order in enqueue.php
3. Ensure specificity is correct
4. Use `!important` only as last resort

## 📊 File Size Comparison

**Before Refactoring:**
- `style.css`: 2,399 lines (everything in one file)

**After Refactoring:**
- `style.css`: 589 lines (75% reduction)
- Split into 7 modular files
- Total: Better organized, easier to maintain

## 🎯 Mobile Shop Page Fix

**File:** `assets/css/08-responsive-mobile.css`  
**Lines:** 17-114

**What it does:**
- Forces 2-column grid on mobile (<768px)
- Overrides desktop list view styles
- Creates compact product cards
- Ensures touch-friendly interface (44px targets)

**Key selectors:**
```css
@media (max-width: 767px) {
    .woocommerce.post-type-archive-product ul.products {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    .woocommerce ul.products li.product.mkz-product-list-item {
        flex-direction: column !important;
        /* Mobile grid card styles */
    }
}
```

## 🚀 Performance Tips

1. **Use CSS variables** - Single source of truth, easy to update
2. **Avoid !important** - Use proper specificity instead
3. **Mobile-first** - Start with mobile, add desktop styles
4. **Minimize media queries** - Use clamp() in variables
5. **Lazy load images** - Already implemented with loading="lazy"

## 📞 Need Help?

- Full documentation: `CSS-REFACTORING-GUIDE.md`
- Code comments in each file explain sections
- All files follow BEM-like naming: `.mkx-component__element--modifier`

---

**Author:** KB - https://kowb.ru  
**Version:** 1.1.0  
**Last Updated:** 2024
