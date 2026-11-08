---
name: AI @ CSU
description: Flat, bordered, green-and-gold wayfinding system for ai.colostate.edu, inherited from the CSU Theme.
colors:
  csu-green: "#1E4D2B"
  csu-green-light: "#245F34"
  csu-green-dark: "#194225"
  csu-gold: "#C8C372"
  gold-tint: "#F1F0DC"
  aggie-orange: "#D9782D"
  oval-green: "#006144"
  horsetooth-blue: "#008FB3"
  flower-trial-red: "#E56A54"
  text-gray: "#59595B"
  theme-black: "#3A3A3A"
  tan: "#E3CDB1"
  white: "#FFFFFF"
typography:
  headline:
    fontFamily: "Poppins, sans-serif"
    fontWeight: 600
  card-title:
    fontFamily: "Poppins, sans-serif"
    fontSize: "22px"
    fontWeight: 700
  body:
    fontFamily: "Poppins, sans-serif"
    fontSize: "clamp(1.1rem, 1.3vw, 1.3rem)"
    fontWeight: 400
  accent-serif:
    fontFamily: "Bodoni Moda, serif"
rounded:
  card: "8px"
  callout: "8px"
spacing:
  grid-gap: "20px"
  callout-gap: "14px"
  card-padding: "24px 22px"
  callout-padding: "28px"
components:
  card:
    backgroundColor: "{colors.white}"
    textColor: "{colors.text-gray}"
    rounded: "{rounded.card}"
    padding: "{spacing.card-padding}"
  card-title:
    textColor: "{colors.csu-green}"
    typography: "{typography.card-title}"
  callout:
    backgroundColor: "{colors.gold-tint}"
    textColor: "{colors.theme-black}"
    rounded: "{rounded.callout}"
    padding: "{spacing.callout-padding}"
  icon-badge:
    backgroundColor: "{colors.csu-gold}"
    textColor: "{colors.csu-green}"
    size: "30px"
---

# Design System: AI @ CSU

## Overview

**Creative North Star: "The Campus Field Guide"**

The site is a practical field guide for the CSU community. Each page names a task, points to the answer, and gets out of the way. The system inherits almost everything from the shared CSU Theme (Poppins, brand green and gold, the theme's own green button) and adds a small set of Elementor-native recipes: bordered cards, a gold-tint callout, and circular icon badges. The visitor arrives with a question about tools, data, or policy; layout is wayfinding first and decoration never.

Density is moderate and calm. Depth comes from borders and tone, not shadows. Brand color carries meaning (green for structure and action, gold for warmth and emphasis); orange and the Energy palette appear sparingly. The theme controls paragraph color, paragraph size, and buttons, so the system works inside those limits rather than fighting them.

**Key Characteristics:**
- Flat and bordered: a 6px green top edge and a 1px outline replace shadows.
- Dark text on light surfaces only; the theme overrides paragraph color on dark backgrounds.
- One H1 per page, ordered headings, Title Case headings and buttons.
- Icons and diagrams, never photo-realistic AI imagery.
- Only brand-approved color pairings; status never relies on color alone.

## Colors

Restrained brand palette: deep green and muted gold on white and tint, with the Energy palette held in reserve. Values come from the CSU Theme variables and brand.colostate.edu/color.

### Primary
- **Colorado State Green** (#1E4D2B): headings, card borders and top edges, icon glyphs, links, theme buttons. Text on white is AAA.
- **Colorado State Gold** (#C8C372): icon badge circles, accents, and the base of the callout tint. Pairs with green text (AA).

### Secondary
- **Aggie Orange** (#D9782D): rare emphasis only.
- **Oval Green** (#006144) and **Horsetooth Blue** (#008FB3): secondary category or status accents, always paired with a text label.

### Neutral
- **Eighty Percent Black** (#59595B): body text on white (AA).
- **Theme Black** (#3A3A3A): dark body text on tints, where #59595B is not an approved pairing.
- **Gold Tint** (#F1F0DC): 25% Gold callout fill. A tint of an approved color, kept with dark text.
- **Tan** (#E3CDB1): optional soft section background; green text only (AA).
- **White** (#FFFFFF): card and page surfaces.

### Named Rules
**The Approved Pairing Rule.** If a background/text pair is not on the brand list, do not use it. AA18 pairs are for large type only.
**The Words-First Status Rule.** Approval or risk is stated in text; color is a second cue, never the only one.

## Typography

**Display and Body Font:** Poppins (inherited from the CSU Theme, sans-serif fallback)
**Accent Font:** Bodoni Moda, used by the theme for select feature text; not introduced in page content.

**Character:** Geometric, open, and friendly with a confident 600-700 weight for headings. Typography is inherited from the theme and Elementor global styles, not set per page.

### Hierarchy
- **Headline** (600, theme-scaled): page H1/H2 in Colorado State Green.
- **Card Title** (700, 22px, left-aligned, green): H3 inside cards.
- **Body** (400, `clamp(1.1rem, 1.3vw, 1.3rem)`): theme-controlled; widget size settings do not reach paragraphs. Keep lines to roughly 65-75ch.
- **Label** (theme button font): buttons in Title Case.

### Named Rules
**The Inherit-It Rule.** Do not set paragraph size or color per widget; the theme ignores it. Set structure and headings, let the theme do the rest.

## Layout

Elementor containers on the theme's page width. Pages are stacks of full-width sections; inside, content is single-column prose or card grids.

Card grid: flex row, wrap, stretch alignment, 20px gap. Three across at 31.5% width, two across at 48.5%, tablet 48%, mobile 100%. Outer containers set `flex_align_items: stretch` and `flex_justify_content: flex-start` or content shrinks and centers. Menus are two levels deep at most. Footer template containers are full width because the theme forces a column direction there. Every page needs desktop, tablet, and mobile behavior checked in a browser.

## Elevation & Depth

Flat by default. No shadows. Hierarchy comes from borders (1px green outline, 6px green top edge on cards, 10px green left edge on callouts), white against tint surfaces, and type weight.

### Named Rules
**The Flat-By-Default Rule.** Surfaces carry no shadow. Do not add elevation to mark importance; use the callout tint and edge instead.

## Shapes

Gently rounded rectangles (8px) for cards and callouts. Icons sit in perfect circles. Rules are straight and structural: top and left edges in solid brand green. No pills, blobs, or decorative geometry.

## Components

### Buttons
- **Style:** the theme's own green `btn` look. Do not set button colors; the theme overrides them.
- **Label:** Title Case, descriptive of the destination.

### Cards
- **Shape:** 8px radius, 1px Colorado State Green border, 6px green top border.
- **Background:** white. **Padding:** 24px 22px.
- **Contents in order:** icon badge, H3 (green, 22px, bold, left), one or two sentences, optional button.
- **Layout:** flex column, stretch alignment, start justification.

### Callout
- **Use:** crisis notices, key warnings, high-priority guidance.
- **Style:** Gold Tint (#F1F0DC) fill, 2px green border with a 10px left edge, 8px radius, 28px padding (18px mobile), 14px gap.
- **Contents:** green H2, dark paragraph, then buttons.

### Icon Badge
- Font Awesome through Elementor's icon widget (inline SVG), stacked circle, Gold (#C8C372) fill, Green (#1E4D2B) glyph, 30px, 0.6em padding, left-aligned.

### Navigation
- Theme main menu, two levels. Utility links (Getting Started, Contact, Governance) sit separately. Primary items: Tools, Teaching, Research, Training, IT Support, News, Resources, About.

## Do's and Don'ts

### Do:
- **Do** build with native Elementor containers and widgets, using theme controls before custom CSS.
- **Do** state status and data limits in words, with color as reinforcement.
- **Do** keep dark text on light backgrounds and use only approved pairings.
- **Do** use real CSU photography (license-checked), icons, or diagrams for imagery.
- **Do** use Title Case for headings and buttons, sentence case for body, and no em dashes.

### Don't:
- **Don't** set white or custom paragraph text on dark backgrounds; the theme overrides it.
- **Don't** recolor theme buttons or rely on paragraph font-size settings.
- **Don't** add shadows, gradients, or decorative effects.
- **Don't** use AI-generated photo-realistic imagery.
- **Don't** nest menus beyond two levels or use HTML widgets and custom code in pushed pages.
