# 📱 Visual Reference Guide - Hobbly App

## App Screenshots Description

### 1. Home Page (Announcements List)
```
┌─────────────────────────────────┐
│  [Logo] Announcements          │
│  [A-Z] [Newest]                │  ← Header with sort buttons
├─────────────────────────────────┤
│  ┌─────────────────────────┐  │
│  │     [Image]             │  │  ← Announcement card
│  │  Title                  │  │
│  │  🏢 Organization        │  │
│  │  Short description...   │  │
│  │  [Tag] [Tag]           │  │
│  └─────────────────────────┘  │
│  ┌─────────────────────────┐  │
│  │     [Image]             │  │  ← More cards...
│  │  Title                  │  │
│  └─────────────────────────┘  │
│  [Loading...]              │  │  ← Infinite scroll
├─────────────────────────────────┤
│ [Home] [Search] [Map]          │  ← Bottom navigation
└─────────────────────────────────┘
```

### 2. Search Page
```
┌─────────────────────────────────┐
│  [Logo] Search                 │  ← Header
│  ┌─────────────────────────┐  │
│  │ 🔍 Search...        [X] │  │  ← Search input
│  └─────────────────────────┘  │
├─────────────────────────────────┤
│  ┌─────────────────────────┐  │
│  │     [Image]             │  │  ← Results (same layout)
│  │  Title                  │  │
│  │  🏢 Organization        │  │
│  └─────────────────────────┘  │
├─────────────────────────────────┤
│ [Home] [Search] [Map]          │  ← Bottom navigation
└─────────────────────────────────┘
```

### 3. Detail Modal (Overlay)
```
┌─────────────────────────────────┐
│                            [X]  │  ← Close button
│  ┌─────────────────────────┐  │
│  │     [Large Image]       │  │
│  └─────────────────────────┘  │
│                                │
│  Title of Announcement         │
│  📍 Organization Name          │
│                                │
│  Full description text that    │
│  can be multiple lines and     │
│  shows the complete story...   │
│                                │
│  [Tag] [Tag] [Tag]            │
│                                │
│  Activity Type                 │
│  Category Name                 │
│                                │
│  Location                      │
│  123 Street Address            │
│                                │
│  Organization Contact          │
│  Name: Organization            │
│  Email: email@example.com      │
│  Phone: 123-456-7890          │
│  Address: 123 Street           │
└─────────────────────────────────┘
```

## Color Palette

### Primary Colors
- **Primary Green**: `#2D5F3F` - Main brand color
- **Light Green**: `#4A9D5F` - Hover states, accents
- **Dark Green**: `#1D4F2F` - Active states

### Neutral Colors
- **Background**: `#FFFFFF` - White
- **Surface**: `#F5F5F5` - Light gray
- **Text**: `#333333` - Dark gray
- **Text Secondary**: `#666666` - Medium gray
- **Border**: `#E0E0E0` - Light gray

## Typography

### Font Family
```
-apple-system, BlinkMacSystemFont, 
'Segoe UI', 'Roboto', 'Oxygen', 
'Ubuntu', 'Cantarell', sans-serif
```

### Font Sizes
- **Extra Small**: 0.75rem (12px) - Tags, meta
- **Small**: 0.875rem (14px) - Body text
- **Base**: 1rem (16px) - Default
- **Large**: 1.125rem (18px) - Card titles
- **Extra Large**: 1.25rem (20px) - Headers
- **2X Large**: 1.5rem (24px) - Page titles

## Spacing System

- **XS**: 0.25rem (4px)
- **SM**: 0.5rem (8px)
- **MD**: 1rem (16px) - Default
- **LG**: 1.5rem (24px)
- **XL**: 2rem (32px)

## Component Sizes

### Header
- Height: 120px
- Fixed position
- Box shadow for depth

### Bottom Navigation
- Height: 65px
- Fixed position
- 3 items equally spaced

### Announcement Cards
- Image height: 200px
- Border radius: 12px
- Padding: 16px
- Gap between cards: 16px

### Touch Targets
- Minimum: 44x44px
- Buttons: 48px height
- Nav items: 65px height

## Animations

### Card Hover
```css
transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
transform: translateY(-4px);
box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
```

### Modal Open
```css
animation: slideUp 0.3s ease;
/* Slides from bottom to top */
```

### Loading Spinner
```css
animation: spin 1s linear infinite;
/* Continuous rotation */
```

## Interactive States

### Button States
1. **Default**: Gray background, dark text
2. **Hover**: Green background, white text
3. **Active**: Darker green, white text
4. **Focus**: Green outline, 2px offset

### Card States
1. **Default**: White background, subtle shadow
2. **Hover**: Elevated, border color changes
3. **Active**: Slight press effect
4. **Focus**: Green outline

## Accessibility Features

### Focus Indicators
- 2px solid green outline
- 2px offset from element
- Visible on all interactive elements

### Color Contrast
- Text on white: Minimum 4.5:1 ratio
- Green on white: Sufficient contrast
- All colors WCAG AA compliant

### Touch Targets
- Minimum 44x44px
- Adequate spacing between targets
- No accidental taps

## Responsive Breakpoints

### Mobile (Default)
- 375px - 575px
- Single column layout
- Full-width cards

### Small Tablet
- 576px+
- Max width: 600px
- Centered content

### Tablet & Desktop
- 768px+
- Modal centered with max-width
- Improved spacing

## Icon Usage

### Navigation Icons
- Home: House icon
- Search: Magnifying glass
- Map: Map icon
- Size: 24x24px

### UI Icons
- Sort: Triangle/bars
- Close: X mark
- Search: Magnifying glass
- Size: 20x20px

## Loading States

### Initial Load
```
┌─────────────────────────┐
│     [Spinner]          │
│  Loading announcements │
└─────────────────────────┘
```

### Infinite Scroll Load
- Small indicator at bottom
- Seamless content addition
- No full-screen overlay

### Empty State
```
┌─────────────────────────┐
│     [Icon]             │
│  No announcements      │
│  Check back later      │
└─────────────────────────┘
```

## Mobile Optimization

### Key Principles
1. **Touch-first**: Large tap targets
2. **Vertical scroll**: No horizontal scroll
3. **Fast loading**: Lazy images, pagination
4. **Smooth animations**: 60fps target
5. **Readable text**: Minimum 14px font size

### 375px Width Specifics
- Single column layout
- Full-width images
- Stacked navigation
- Modal full-screen on mobile
- Adequate padding (16px)

## Performance Targets

### Lighthouse Scores
- Performance: > 90
- Accessibility: 100
- Best Practices: > 90
- SEO: > 90

### Key Metrics
- First Contentful Paint: < 1.5s
- Time to Interactive: < 3.5s
- Speed Index: < 3.0s
- Total Blocking Time: < 200ms

## Brand Elements

### Logo
- Location: Top left of header
- Size: 48x48px
- Format: PNG (green_logo@low-res.png)

### Color Usage
- Primary green: Headers, buttons, links
- Light green: Hover states, tags
- Dark green: Active states
- Neutral grays: Text, backgrounds

## User Flow

### Viewing Announcements
1. Land on home page
2. See first 10 announcements
3. Scroll down → Load more (infinite)
4. Click card → See detail modal
5. Close modal → Return to list

### Searching
1. Navigate to Search page
2. Type keyword
3. See filtered results (debounced)
4. Scroll for more results
5. Click card → See detail modal

### Navigation
1. Tap bottom nav item
2. Page transitions smoothly
3. Active state shows current page

---

## Design Principles Applied

✅ **Mobile-First**: Designed for 375px, enhanced for larger
✅ **Accessibility**: WCAG 2.1 AA compliant throughout
✅ **Performance**: Optimized loading and rendering
✅ **Clarity**: Clear hierarchy and information
✅ **Consistency**: Unified design system
✅ **Simplicity**: No unnecessary elements
✅ **Feedback**: Clear states and responses

---

## Testing Views

### Desktop Testing
```
Chrome DevTools → Toggle Device Toolbar
Device: iPhone SE (375x667)
Test: Scroll, tap, search, navigate
```

### Lighthouse Audit
```
DevTools → Lighthouse → Mobile
Categories: All
Generate Report → Review scores
```

### Accessibility Audit
```
axe DevTools Extension
Scan All of Page
Fix any critical/serious issues
```

---

**This visual guide helps understand the app's appearance and behavior!**
