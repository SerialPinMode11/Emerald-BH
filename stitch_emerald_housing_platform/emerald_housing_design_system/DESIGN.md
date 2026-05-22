---
name: Emerald Housing Design System
colors:
  surface: '#fdf7ff'
  surface-dim: '#ded8e0'
  surface-bright: '#fdf7ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f8f2fa'
  surface-container: '#f2ecf4'
  surface-container-high: '#ece6ee'
  surface-container-highest: '#e6e0e9'
  on-surface: '#1d1b20'
  on-surface-variant: '#494551'
  inverse-surface: '#322f35'
  inverse-on-surface: '#f5eff7'
  outline: '#7a7582'
  outline-variant: '#cbc4d2'
  surface-tint: '#6750a4'
  primary: '#4f378a'
  on-primary: '#ffffff'
  primary-container: '#6750a4'
  on-primary-container: '#e0d2ff'
  inverse-primary: '#cfbcff'
  secondary: '#63597c'
  on-secondary: '#ffffff'
  secondary-container: '#e1d4fd'
  on-secondary-container: '#645a7d'
  tertiary: '#765b00'
  on-tertiary: '#ffffff'
  tertiary-container: '#c9a74d'
  on-tertiary-container: '#503d00'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#e9ddff'
  primary-fixed-dim: '#cfbcff'
  on-primary-fixed: '#22005d'
  on-primary-fixed-variant: '#4f378a'
  secondary-fixed: '#e9ddff'
  secondary-fixed-dim: '#cdc0e9'
  on-secondary-fixed: '#1f1635'
  on-secondary-fixed-variant: '#4b4263'
  tertiary-fixed: '#ffdf93'
  tertiary-fixed-dim: '#e7c365'
  on-tertiary-fixed: '#241a00'
  on-tertiary-fixed-variant: '#594400'
  background: '#fdf7ff'
  on-background: '#1d1b20'
  surface-variant: '#e6e0e9'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Roboto Flex
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Roboto Flex
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
  label-sm:
    fontFamily: Fira Sans
    fontSize: 14px
    fontWeight: '500'
    lineHeight: '1.2'
  code-sm:
    fontFamily: JetBrains Mono
    fontSize: 13px
    fontWeight: '400'
    lineHeight: '1.5'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  margin-mobile: 16px
  margin-desktop: 64px
  gutter: 24px
  stack-xs: 4px
  stack-md: 16px
  stack-lg: 32px
---

## Brand & Style
The design system for this multi-tenant property rental platform is built on the pillars of **Modernity, Trust, and Professionalism**. It utilizes a sophisticated **Glassmorphism** aesthetic to create depth and visual interest while maintaining the high clarity required for real estate transactions.

The brand experience is tailored to five distinct user personas (Customer, Land Owner, Community, Super Admin, and Dev Admin), each signaled through a unique color logic. The UI evokes a sense of transparency and security through the use of frosted-glass layers, soft background blurs, and a rigorous typographic hierarchy. This approach ensures the platform feels like a premium service rather than a generic utility.

## Colors
This design system employs a multi-tenant color strategy. The **Primary** color is used for key action buttons, brand signifiers, and active states. The **Secondary** color provides a soft background tint for large surfaces to differentiate the portal environment visually. The **Accent** color is reserved for highlights, notifications, or call-to-action modifiers.

The default background remains a clean, neutral white or light gray, allowing the glassmorphic cards to pop. Each tenant's primary color should be used as a subtle tint within the glass blur to maintain brand immersion.

## Typography
The typography system balances authority and legibility. **Inter** is utilized for headlines to provide a modern, structural feel. **Roboto** (using the Flex variant for enhanced spacing control) handles body text to ensure maximum readability across long property descriptions and data tables. 

For technical panels and administrative metadata, **JetBrains Mono** (as a high-quality alternative to Fira Code) is used to distinguish system-level data from user-generated content. Headlines should use tighter tracking for a more "designed" look, while body text maintains standard tracking for accessibility.

## Layout & Spacing
The layout follows a **Mobile-First** philosophy. On mobile devices, a single-column fluid layout with 16px side margins is standard. As the viewport scales to desktop, the system transitions to a 12-column grid with a maximum content width of 1280px.

Spacing is based on an 8px linear scale to ensure consistent rhythm. Use larger vertical "stack" spacing (32px+) to separate distinct sections of a property listing, while using smaller increments (8px-16px) for internal card elements.

## Elevation & Depth
Depth is created through **Glassmorphism**. Surfaces do not use traditional heavy drop shadows. Instead, they utilize backdrop blurs (12px) and semi-transparent white backgrounds (60% opacity) to create a "frosted" effect.

Each glass element must have a 1px solid border with low opacity (30% white) to define its edges against varying backgrounds. Higher elevation levels are achieved by increasing the backdrop blur and adding a very soft, diffused ambient shadow. Admin panels should use higher contrast glass (lower transparency) to focus on data density.

## Shapes
The shape language is **Rounded**, reflecting a friendly yet professional demeanor. Standard UI components like buttons and input fields use a 0.5rem (8px) radius. Larger containers, such as property feature cards and modal overlays, should use the `rounded-lg` (16px) or `rounded-xl` (24px) tokens to emphasize the glass effect and soften the overall aesthetic.

## Components
- **Glass Cards:** The primary container. Must include `backdrop-filter: blur(12px)`, a thin semi-transparent border, and a subtle inner glow.
- **Buttons:** Primary buttons use the tenant’s `primary_color`. They should be solid with high-contrast text. Secondary buttons should use a glass effect with a primary-colored border.
- **Inputs:** Fields should be semi-transparent with a 1px border that darkens/colors on focus. Labels use `label-sm` for clarity.
- **Chips:** Used for property tags (e.g., "Available", "Pet Friendly"). Use the `secondary_color` as a background with `primary_color` for text.
- **Lists:** Real estate listings should be presented in glass containers with generous padding and clear `headline-md` titles.
- **Technical Panels:** Exclusive to Dev Admin; use a darker glass variant (30% black) with `code-sm` JetBrains Mono text for logs and configurations.
- **Tenant Switcher:** A prominent UI element for Super Admins to toggle between views, styled as a segmented glass controller.