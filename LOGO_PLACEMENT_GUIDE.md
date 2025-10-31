# 🎨 Hobbly Logo Placement Guide

```
┌─────────────────────────────────────────┐
│  📱 MOBILE APP LAYOUT (375px)          │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ ╔═════════════════════════════════════╗ │
│ ║  🎯 HEADER                          ║ │
│ ║  [Logo] Events    [A-Z] [Newest]   ║ │ ← logo_primary_1@high-res.png (56px)
│ ╚═════════════════════════════════════╝ │
│                                          │
│ ┌────────────────────────────────────┐  │
│ │  🔄 LOADING STATE                  │  │
│ │                                    │  │
│ │         [Pulsing Logo]            │  │ ← logo_primary_1@high-res.png (64px)
│ │           ⚪ Loading...            │  │   with pulse animation
│ │                                    │  │
│ └────────────────────────────────────┘  │
│                                          │
│ ┌────────────────────────────────────┐  │
│ │  📝 EVENT CARDS                    │  │
│ │  ┌──────────────────────────────┐ │  │
│ │  │ [Event Image]                │ │  │
│ │  │ Event Title                  │ │  │
│ │  │ Organization Name            │ │  │
│ │  └──────────────────────────────┘ │  │
│ │  Content appears over subtle      │  │
│ │  background watermark            │  │ ← logo_secondary@low-res.png
│ └────────────────────────────────────┘  │   (300px, 2% opacity)
│                                          │
│ ╔═════════════════════════════════════╗ │
│ ║  🏠 BOTTOM NAV                      ║ │
│ ║  [Home] [Search] [Map]             ║ │
│ ╚═════════════════════════════════════╝ │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  ❌ EMPTY STATE                         │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│                                          │
│                                          │
│           [Large Faded Logo]            │ ← logo_secondary@high-res.png
│                                          │   (120px, 30% opacity)
│         No events found                 │
│    Check back later for activities      │
│                                          │
│                                          │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  📋 MODAL / DETAIL VIEW                 │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ ╔═════════════════════════════════════╗ │
│ ║              [Close X]               ║ │
│ ╚═════════════════════════════════════╝ │
│                                          │
│  [Large Event Image]                    │
│                                          │
│  Event Title                            │
│  📍 Organization Name                   │
│                                          │
│  Full description text...               │
│                                          │
│  🏃 Activity Type                       │
│  🎯 Category                            │
│  📍 Address                             │
│  🔗 Website                             │
│                                          │
│  📞 Contact Information                 │
│     Organization: Name                  │
│     Email: email@example.com            │
│     Phone: +358 123 456                 │
│                                          │
│ ┌────────────────────────────────────┐  │
│ │  [🎯] Powered by Hobbly            │  │ ← logo_primary@low-res.png (24px)
│ └────────────────────────────────────┘  │   Modal footer branding
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  🌐 BROWSER TAB                         │
└─────────────────────────────────────────┘

[🎯] Hobbly - Events                      ← logo_primary@low-res.png
                                             as favicon

┌─────────────────────────────────────────┐
│  📊 LOGO USAGE SUMMARY                  │
└─────────────────────────────────────────┘

1. PRIMARY LOCATIONS (Always Visible)
   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   📍 Header Logo (56px)
   🔄 Loading Logo (64px with animation)
   ❌ Empty State (120px, faded)
   🌐 Favicon (browser tab)

2. SECONDARY LOCATIONS (Contextual)
   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   📋 Modal Footer (24px with text)

3. BACKGROUND (Ultra Subtle)
   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   🎨 Background Watermark (300px, 2% opacity)

┌─────────────────────────────────────────┐
│  🎨 LOGO VARIANTS & USAGE               │
└─────────────────────────────────────────┘

Logo File                    │ Size  │ Opacity │ Location
─────────────────────────────┼───────┼─────────┼──────────────────
logo_primary_1@high-res.png  │ 56px  │ 100%    │ Header
logo_primary_1@high-res.png  │ 64px  │ 60-100% │ Loading (animated)
logo_secondary@high-res.png  │ 120px │ 30%     │ Empty state
logo_primary@low-res.png     │ 24px  │ 50%     │ Modal footer
logo_secondary@low-res.png   │ 300px │ 2%      │ Background
logo_primary@low-res.png     │ 16px  │ 100%    │ Favicon

┌─────────────────────────────────────────┐
│  💡 DESIGN NOTES                        │
└─────────────────────────────────────────┘

✅ HIGH-RES logos for main UI elements
✅ LOW-RES logos for background/small sizes
✅ PRIMARY logo for active elements
✅ SECONDARY logo for passive/background
✅ Consistent green color scheme throughout
✅ Opacity used to create hierarchy
✅ Animations on loading state only

┌─────────────────────────────────────────┐
│  🎯 BRANDING OBJECTIVES ACHIEVED        │
└─────────────────────────────────────────┘

✅ Professional appearance
✅ Strong brand recognition
✅ Non-intrusive placement
✅ Consistent visual identity
✅ Accessible (alt text on all)
✅ Performant (optimized sizes)
✅ Mobile-responsive
✅ Subtle yet memorable

```

---

## Quick Reference

**Header Logo**: Big & bold - Primary brand presence  
**Loading Logo**: Animated - Engaging wait experience  
**Empty State Logo**: Large & faded - Friendly void filler  
**Modal Logo**: Small & subtle - Professional signature  
**Background Logo**: Barely visible - Subliminal branding  
**Favicon**: Tiny & recognizable - Tab identification  

All logos work together to create a cohesive, professional brand experience! 🎉
