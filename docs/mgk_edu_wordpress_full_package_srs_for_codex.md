# MGK EDU — WordPress Full-Package Implementation SRS for Codex

## 0. Document Purpose

This document is the **WordPress implementation SRS** for `mgk_edu`.

It rewrites the broader product SRS into a practical specification that matches the current implementation direction:

```text
WordPress full package-flow
Local runtime first
Then release full WordPress source + database
```

This document is written for Codex so it can inspect, build, and fix `mgk_edu` logic without drifting into a generic SaaS architecture too early.

---

## 1. Core Product Understanding

`mgk_edu` is a WordPress template for a K-12 1-to-1 tutoring platform in Singapore.

Default business model:

```text
Agency-mode
```

Meaning:

```text
The paying customer is the tuition agency.
The website helps parents/students discover tutors, request matches, book trial lessons, and later manage lessons/packages.
```

Primary visitor conversion:

```text
Anonymous parent/student
→ search/filter tutors
→ view tutor profile
→ request match or book trial
```

Current build priority:

```text
Batch 1 Public Discovery: S01-S06
```

Future flows must be considered, but not fully implemented yet:

```text
S07-S12 Booking & Match
S13-S18 Parent Dashboard
S19-S24 Tutor Portal
S25-S27 Agency Admin
```

---

## 2. WordPress Full Package-Flow Rule

This project must follow **Direction B: full package-flow**.

Do not treat the final deliverable as only:

```text
theme zip + seed script
```

The final release must be:

```text
$template_name.sql
$template_name.tar
```

Where:

```text
.sql = full database dump
.tar = full WordPress source from runtime data directory
```

Therefore Codex must always think in this order:

```text
Edit package child theme source
→ sync into local runtime
→ update WordPress DB/pages/options/CPT data if needed
→ test local routes
→ snapshot/release only when explicitly requested
```

Do not release unless the user explicitly asks.

---

## 3. Current Implementation Scope

### In scope now

```text
S01 Home
S02 Tutor Listing
S03 Tutor Profile
S04 Subject Catalog
S05 How It Works
S06 Pricing
```

### Prepare hooks only, not full implementation yet

```text
S07 Request Form
S08 Proposal Page
S09-S12 Booking + Payment
S13-S18 Parent Dashboard
S19-S24 Tutor Portal
S25-S27 Agency Admin
```

### Not in scope for current Batch 1 implementation

```text
real PayNow webhook
real Stripe payment
real WhatsApp Business API
real SMS OTP
real OCR
real background check service
real WebSocket infra
full multi-tenant SaaS dashboard
```

For those, create safe placeholder functions, mock responses, data abstractions, and route stubs only.

---

## 4. How to Read the Wireframe HTML

Use:

```text
MGK-Wireframe-Batch1-v2-COMPLETE.html
```

The wireframe is not production HTML.

Only extract production UI from:

```text
.screen-section
  > .breakpoint-row
    > .bp-col
      > .canvas
        > .device
```

Do not render these documentation/spec elements:

```text
.spec-topbar
.legend
.intro
.screen-header
.screen-meta-bar
.section-toc
.breakpoint-row
.bp-col
.bp-label
.canvas
.device
.annotations
.annotation
.pin
.state-variants
.state-chip
.sec-label
```

Use desktop markup as canonical.  
Use tablet/mobile wireframe previews only to define responsive behavior.

---

## 5. Target WordPress Theme Structure

Codex should create or maintain this structure inside the actual `flatsome-child` package.

```text
flatsome-child/
  style.css
  functions.php
  header.php
  footer.php
  404.php

  inc/
    mgk-assets.php
    mgk-setup.php
    mgk-helpers.php
    mgk-demo-data.php
    mgk-routes.php
    mgk-rest.php
    mgk-cpt.php
    mgk-forms.php
    mgk-pricing.php
    mgk-analytics.php

  assets/
    css/
      mgk-tokens.css
      mgk-base.css
      mgk-layout.css
      mgk-components.css
      mgk-states.css
      mgk-home.css
      mgk-listing.css
      mgk-profile.css
      mgk-subjects.css
      mgk-how.css
      mgk-pricing.css
      mgk-responsive.css

    js/
      mgk-main.js
      mgk-search.js
      mgk-filters.js
      mgk-forms.js
      mgk-sticky-cta.js
      mgk-pricing.js
      mgk-analytics.js

  template-parts/
    layout/
      site-header.php
      site-footer.php
      mobile-nav.php

    components/
      button.php
      search-form.php
      tutor-card.php
      subject-card.php
      trust-stat.php
      review-card.php
      faq-accordion.php
      pricing-card.php
      cta-band.php
      filter-sidebar.php
      compare-drawer.php
      breadcrumb.php

    states/
      loading-skeleton.php
      empty-results.php
      form-error.php
      validation-message.php
      server-error.php
      not-found-panel.php
      offline.php
      permission-denied.php
      session-expired.php

    sections/
      home/
      listing/
      profile/
      subjects/
      how/
      pricing/

  page-home.php
  front-page.php
  page-tutors.php
  single-mg_teacher.php
  page-subjects.php
  page-how-it-works.php
  page-pricing.php

  schemas/
  seed/
```

If the real repo already has a different structure, adapt without destroying existing working files.

---

## 6. WordPress Data Strategy

The current MVP can use helper data or CPTs. Do not hardcode repeated data directly inside page templates.

### Recommended CPTs

```text
mg_teacher
mg_subject
mg_lead
mg_booking
mg_lesson
mg_review
mg_plan
```

### Recommended taxonomies

```text
mg_subject_tax
mg_level
mg_tutor_tier
mg_location_area
```

### Required helper functions

These functions may use CPTs if available, otherwise demo arrays.

```php
mgk_get_tutors(array $filters = []): array
mgk_get_featured_tutors(int $limit = 8): array
mgk_get_tutor(string|int $id_or_slug): ?array
mgk_get_subjects(array $args = []): array
mgk_get_featured_subjects(int $limit = 12): array
mgk_get_reviews(array $args = []): array
mgk_get_faqs(string $scope = 'general'): array
mgk_get_pricing_config(): array
mgk_get_site_config(): array
```

### Pricing helpers

```php
mgk_calculate_trial_price(float $hourly_rate, int $duration_min = 60): array
mgk_calculate_package_price(float $hourly_rate, int $lessons, array $discounts = []): array
mgk_calculate_pricing_estimate(array $payload): array
```

### Lead/request helpers

```php
mgk_validate_sg_phone(string $phone): bool
mgk_validate_lead_payload(array $payload): array
mgk_create_lead(array $payload): array|WP_Error
mgk_get_lead_state(string $lead_id): string
```

### Slot helpers, placeholder for current phase

```php
mgk_get_available_slots(int|string $tutor_id, string $from, string $to): array
mgk_hold_slot(int|string $slot_id, int|string $lead_id): array|WP_Error
mgk_release_slot(int|string $slot_id, string $hold_token): bool
```

### Tracking helper

```php
mgk_track_event(string $event_name, array $properties = []): void
```

For Batch 1, the tracking helper can be a JS/dataLayer stub.

---

## 7. WordPress Route Matrix

Codex must inspect the real project and use actual route names, but the intended route matrix is:

| Screen | Route | WordPress Template | Purpose |
|---|---|---|---|
| S01 Home | `/` | `front-page.php` or `page-home.php` | Main discovery and conversion page |
| S02 Tutor Listing | `/student/teachers/` | `page-tutors.php` | Search/filter tutor list |
| S03 Tutor Profile | `/teacher/{slug}/` or CPT permalink | `single-mg_teacher.php` | Convert profile view to trial/request |
| S04 Subjects | `/subjects/` | `page-subjects.php` | SEO subject discovery |
| S05 How It Works | `/how-it-works/` | `page-how-it-works.php` | Explain flow to parents/tutors/agencies |
| S06 Pricing | `/pricing/` | `page-pricing.php` | Transparent pricing calculator |
| Future Request | `/parent/trial/` or `/request-match/` | future page/shortcode | Request or trial booking |
| Login | `/login/` | existing auth route | Magic link/login placeholder |
| Profile | `/profile/` | existing account route | Authenticated user profile |

If route does not exist, Codex should create the required page, template, or rewrite in a minimal WordPress-safe way.

---

## 8. CTA and Button Function Matrix

Button behavior is part of business logic. Buttons must not be only visual.

### 8.1 Global button component

Create a reusable button component:

```php
mgk_button(array $args): string
```

Suggested args:

```php
[
  'label' => 'Find Tutor',
  'url' => '/student/teachers/',
  'variant' => 'primary',       // primary|secondary|ghost|dark|white
  'size' => 'md',               // sm|md|lg
  'icon' => 'arrow-right',
  'attrs' => [
    'data-event' => 'cta_click',
    'data-cta' => 'find_tutor'
  ],
  'disabled' => false,
  'loading' => false
]
```

Output must include:

```text
safe escaped href
ARIA label when needed
data-event tracking attributes
consistent CSS classes
```

### 8.2 Main CTA buttons

| Button | Where | Function |
|---|---|---|
| Find Tutor | Header, S01 hero, S01 final CTA, S05, S06 | Go to tutor listing or request flow |
| Search 50,000+ Tutors | S01 hero search | Submit search fields to S02 with query params |
| Browse Tutors | S01/S05 | Go to `/student/teachers/` |
| View Profile | Tutor cards | Go to S03 tutor profile |
| Book Trial | S03 profile | Go to trial/request route with `tutor` param |
| Request Custom Time | S03 availability | Open message/request flow with tutor context |
| Add to Compare | S02 card | Toggle compare drawer, max 3 tutors |
| Compare | S02 drawer | Open compare modal/page |
| Clear All Filters | S02 | Remove URL filter params and reload list |
| Apply Filters | S02 mobile filter | Apply selected filters and close bottom sheet |
| View All Subjects | S01/S04 | Go to subject catalog |
| Subject Card | S01/S04 | Go to listing with `subject` param |
| Find Matching Tutors | S06 calculator | Go to listing with level/subject/budget from calculator |
| Apply as Tutor | Header/Footer | Go to future tutor apply route |
| For Agency | Utility/Header/Footer | Go to future agency landing/contact route |
| Sign In | Header | Go to `/login/` |
| Newsletter Submit | S01 | Validate email, show success/error state |
| FAQ Toggle | S01/S05/S06 | Expand/collapse accessible FAQ item |

### 8.3 Search form function

Hero search must build URL:

```text
/student/teachers/?subject={subject}&level={level}&area={location}&budget_max={budget}
```

If a field is empty, omit that query param.

JS function:

```js
mgkBuildTutorSearchUrl(formData)
mgkSubmitTutorSearch(event)
```

PHP helper:

```php
mgk_get_tutor_listing_url(array $params = []): string
```

### 8.4 Pricing calculator button function

Pricing calculator CTA must build URL:

```text
/student/teachers/?level={level}&subject={subject}&tier={tier}&budget_min={min}&budget_max={max}
```

JS functions:

```js
mgkCalculatePricingEstimate(state)
mgkUpdatePricingResult(result)
mgkBuildPricingSearchUrl(state)
```

### 8.5 Compare drawer function

Rules:

```text
Selected tutors min = 1
Selected tutors max = 3
When selected = 0, drawer hidden
When selected > 0, drawer sticky bottom
When user attempts > 3, show toast
```

JS functions:

```js
mgkToggleCompare(tutorId)
mgkRenderCompareDrawer()
mgkOpenCompareModal()
mgkClearCompare()
```

### 8.6 Sticky CTA function

Rules:

```text
Appears after user scrolls past hero
Hidden while user is typing in form fields
Mobile bottom-fixed
Desktop sticky rail only where wireframe indicates
```

JS functions:

```js
mgkInitStickyCta()
mgkShouldShowStickyCta()
mgkTrackStickyCtaClick()
```

---

## 9. Business Rules for WordPress MVP

These business rules must be reflected in copy, UI, helper logic, or placeholders.

### BR-WP-01 Trial discount

```text
Trial lesson is 40% off hourly rate.
```

For Batch 1, show correct pricing copy and calculator estimate.

### BR-WP-02 No signup before request

```text
Anonymous user can browse S01-S06 and submit initial request later.
Do not force login on public discovery pages.
```

### BR-WP-03 Matching SLA

```text
Copy should promise proposal/match target of 6 hours where used.
Future lead logic should store `sla_due_at = created_at + 6h`.
```

### BR-WP-04 Verified tutor display

```text
Only show verified badge/credential if tutor verification_state allows it.
Do not show unverified documents as verified.
```

### BR-WP-05 Demo video as trust signal

```text
Tutor profile should place demo video prominently.
If demo video missing, show safe placeholder and do not break layout.
```

### BR-WP-06 Pricing transparency

```text
Pricing page must show what is included and not included.
Avoid hidden fee UX.
```

### BR-WP-07 Query persistence

```text
Filters must persist in URL so results are shareable.
```

### BR-WP-08 Empty states

```text
If no tutors/subjects/reviews/pricing config exist, show reusable empty state.
No fatal errors.
```

### BR-WP-09 Privacy

```text
Do not expose NRIC, full phone, home address, parent address, or raw verification docs.
```

### BR-WP-10 Release safety

```text
Do not run snapshot or release unless explicitly requested.
```

---

## 10. Screen-by-Screen WordPress SRS

## 10.1 S01 Home

### Purpose

Convert visitor into tutor search/request interest within a short session.

### Template

```text
front-page.php or page-home.php
```

### Required sections

```text
1. Utility bar
2. Main nav
3. Hero + inline search
4. Trust bar
5. Live activity feed
6. How it works
7. Browse by subject
8. Featured tutors
9. Why choose us
10. Tutor of the month
11. Success stories
12. Reviews
13. FAQ
14. Pricing teaser
15. Press logos
16. Final CTA
17. Newsletter
18. Footer
```

### Required functions

```php
mgk_get_featured_tutors(8)
mgk_get_subjects(['featured' => true])
mgk_get_reviews(['limit' => 3])
mgk_get_faqs('home')
mgk_get_tutor_listing_url($params)
```

### Required button logic

```text
Hero search submit → S02 with query params
Find Tutor → S02
Browse Tutors → S02
View Profile → S03
Book Trial → future request/trial route with tutor param
Pricing teaser → S06
Newsletter → validate email and show state
FAQ → accessible accordion
```

### Acceptance criteria

```text
Home loads at `/`.
Hero search produces correct URL.
Featured tutors use data helper/CPT.
Subject cards route to listing with subject param.
Newsletter shows validation.
No wireframe spec artifacts visible.
Responsive at desktop/tablet/mobile.
```

---

## 10.2 S02 Tutor Listing

### Purpose

Help parent/student narrow tutor list quickly.

### Template

```text
page-tutors.php
```

### Required sections

```text
listing header/search summary
filter sidebar desktop
filter modal/bottom-sheet mobile
active filter chips
result count
sort dropdown
tutor cards
compare drawer
pagination/load more
empty state
loading skeleton
```

### Required query params

```text
subject
level
area
budget_min
budget_max
tier
rating
availability
online
sort
page
```

### Filter logic

```text
OR within same filter group
AND across filter groups
URL params must persist selected state
```

### Required functions

```php
mgk_get_tutors($filters)
mgk_parse_tutor_filters($_GET)
mgk_filter_tutors($tutors, $filters)
mgk_sort_tutors($tutors, $sort)
mgk_get_active_filter_chips($filters)
```

### Required JS

```js
mgkInitFilters()
mgkApplyFilters()
mgkClearFilters()
mgkRemoveFilterChip()
mgkInitCompareDrawer()
```

### Acceptance criteria

```text
S01 search params are read correctly.
Result count updates.
No results shows empty state.
Mobile filter opens as bottom sheet.
Compare drawer appears at 1 selected tutor and blocks more than 3.
Tutor cards contain required data.
```

---

## 10.3 S03 Tutor Profile

### Purpose

Convert profile view into trial/request intent.

### Template

```text
single-mg_teacher.php
```

### Required sections

```text
breadcrumb/back
hero with avatar/name/price/CTA
5 trust signals
demo video
quick info bar
about
verified qualifications
track record stats
availability calendar
lesson packages
reviews breakdown
gallery
FAQ
similar tutors
recently viewed
sticky CTA
footer
```

### Required functions

```php
mgk_get_tutor($slug)
mgk_get_tutor_trust_signals($tutor)
mgk_get_tutor_credentials($tutor, ['verified_only' => true])
mgk_get_tutor_reviews($tutor_id)
mgk_get_available_slots($tutor_id, $from, $to)
mgk_get_similar_tutors($tutor)
```

### Required button logic

```text
Book Trial → future request/trial route with tutor prefilled
Request Custom Time → future request/message route with tutor prefilled
Watch Demo → open video modal
Availability Slot → future booking route with tutor + slot prefilled
Similar Tutor Card → that tutor profile
Sticky CTA → same as Book Trial
```

### Acceptance criteria

```text
Invalid tutor slug shows not-found state.
Only verified credentials appear.
Missing demo video shows placeholder.
No reviews shows no-review state.
Sticky CTA appears after hero on mobile.
```

---

## 10.4 S04 Subject Catalog

### Purpose

SEO landing and visual discovery for subject-based tutor search.

### Template

```text
page-subjects.php
```

### Required sections

```text
hero
quick search
browse by level
browse by exam
popular combinations
trending subjects
stream/specialty
international curriculum
featured subject deep dive
help finding subject CTA
footer
```

### Required functions

```php
mgk_get_subjects()
mgk_get_subject_groups()
mgk_get_exam_groups()
mgk_get_subject_listing_url($subject_slug)
```

### Required button logic

```text
Subject card → S02 with subject query
Level card → S02 with level query
Exam card → S02 with exam/level query
Quick search → S02 with subject keyword
Help finding subject → request/help route
```

### Acceptance criteria

```text
Subject slugs are URL-safe.
Subject cards do not break if count is zero.
All subject CTAs route to listing.
Mobile layout is single-column/accordion-friendly.
```

---

## 10.5 S05 How It Works

### Purpose

Explain the platform flow for parents, tutors, and agencies.

### Template

```text
page-how-it-works.php
```

### Required sections

```text
hero
audience tabs: Parent / Tutor / Agency
4-step process per tab
verification explanation
guarantee/replacement
comparison vs traditional agency
FAQ
final CTA
```

### Required button logic

```text
Parent tab CTA → S02 or request route
Tutor tab CTA → future tutor apply route
Agency tab CTA → future agency contact route
FAQ toggle → expand/collapse
```

### Required JS

```js
mgkInitHowTabs()
```

### Acceptance criteria

```text
Tabs are keyboard accessible.
No-JS fallback shows content or first tab safely.
CTA differs by selected tab.
FAQ works on mobile.
```

---

## 10.6 S06 Pricing

### Purpose

Make pricing transparent and drive users to matching tutor search.

### Template

```text
page-pricing.php
```

### Required sections

```text
hero
pricing calculator
hourly rate table
subject premium
package comparison
what is not included
FAQ
final CTA
```

### Pricing calculator inputs

```text
level
subject
tutor tier
duration
frequency
package
```

### Output

```text
estimated hourly rate
per lesson estimate
monthly estimate
package total
saving amount
```

### Required functions

```php
mgk_get_pricing_config()
mgk_calculate_pricing_estimate($payload)
mgk_get_pricing_search_url($payload)
```

### Required JS

```js
mgkInitPricingCalculator()
mgkCalculatePricingEstimate()
mgkUpdatePricingResult()
mgkBuildPricingSearchUrl()
```

### Required button logic

```text
Find Matching Tutors → S02 with calculator state as query params
Package CTA Trial → request/trial route
Package CTA 8/16 → S02/request route with package param
FAQ toggle → expand/collapse
```

### Acceptance criteria

```text
Calculator updates without page reload.
Invalid/missing input shows validation state.
Package 8 marked most popular.
Pricing disclaimer visible.
CTA carries level/subject/budget context to listing.
```

---

## 11. REST / AJAX Mapping for WordPress

Use WordPress REST namespace:

```text
/wp-json/mgk/v1/
```

Do not implement external service calls in Batch 1.

### MVP endpoints

```http
GET /wp-json/mgk/v1/tutors
GET /wp-json/mgk/v1/tutors/{slug}
GET /wp-json/mgk/v1/subjects
GET /wp-json/mgk/v1/pricing/estimate
POST /wp-json/mgk/v1/leads
POST /wp-json/mgk/v1/track
```

### Endpoint behavior

| Endpoint | Current Batch 1 behavior |
|---|---|
| tutors | Return demo/CPT tutor list with filters |
| tutors/{slug} | Return single tutor or 404 |
| subjects | Return subject list/counts |
| pricing/estimate | Return calculated pricing estimate |
| leads | Validate payload and store mock/CPT lead if implemented |
| track | Accept event payload or no-op safely |

### Security

```text
Public GET endpoints can be open.
POST endpoints must use nonce, rate limit placeholder, and sanitization.
Never store raw unsafe input.
```

---

## 12. Form and Validation Rules

### Common validation states

```text
idle
focus
valid
invalid
submitting
success
server_error
```

### Reusable state partials

```text
form-error.php
validation-message.php
server-error.php
loading-skeleton.php
empty-results.php
not-found-panel.php
```

### SG phone validation

For future request forms:

```text
+65
8 digits
starts with 6, 8, or 9
```

### Budget validation

```text
Optional.
If present, accepted hourly range = SGD 25-200.
```

### Email validation

```text
Required for newsletter/lead.
Use WordPress sanitize_email + is_email.
```

### Note field

```text
Max 500 characters.
```

---

## 13. Tracking Events

Add `data-event` and call `mgk_track_event`/`dataLayer` stub.

Required Batch 1 events:

```text
page_view
cta_click
search_submit
filter_apply
filter_clear
tutor_card_click
compare_add
compare_open
tutor_profile_view
demo_video_play
availability_slot_click
subject_card_click
pricing_calculator_change
pricing_cta_click
faq_toggle
newsletter_submit
```

Example JS:

```js
window.mgkTrack = function(eventName, payload = {}) {
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({ event: eventName, ...payload });
};
```

---

## 14. Privacy and Security Rules for WordPress Pages

Do not show:

```text
NRIC / FIN
raw verification documents
full tutor phone number
full parent phone number
parent home address
full tutor legal name if display name exists
private payment data
```

Public tutor profile may show:

```text
display name
public avatar
verified badge
verified credential summary
rating
reviews
subjects
teaching areas
online availability
hourly rate
demo video
```

Address appears only after booking confirmation in future flow.

---

## 15. Agency Configurability

Avoid hardcoding agency-specific values. Use config helper:

```php
mgk_get_site_config()
```

Configurable values:

```text
agency logo
agency name
phone
email
WhatsApp link
primary/accent color
PayNow UEN placeholder
Stripe enable flag
commission model A/B
pricing tiers
trial discount
package discounts
subject list
featured tutors
footer legal links
DPO contact email
```

Suggested storage:

```text
WordPress options for current MVP
future admin wizard later
```

Option names:

```text
mgk_agency_name
mgk_agency_phone
mgk_agency_email
mgk_agency_whatsapp
mgk_accent_color
mgk_commission_model
mgk_paynow_uen
mgk_pricing_config
mgk_feature_flags
```

---

## 16. Full Package Runtime Requirements

After each meaningful implementation phase, Codex should:

```text
1. Sync child theme source into runtime.
2. Run PHP lint.
3. Verify local route.
4. Check no fatal errors.
5. Report changed files.
```

Do not run release unless explicitly requested.

Before final release, local runtime must include:

```text
pages
post content
options
active theme
CPT data
demo teachers/plans/faqs
plugin state if needed
route/permalink state
```

Because platform release uses full source + DB.

---

## 17. QA Checklist for Codex

Before claiming a phase is done, check:

```text
PHP lint passes.
The intended route loads.
No spec/annotation HTML visible.
Responsive layout works at 1440, 768, 390.
Buttons route correctly.
Forms validate.
Empty states work.
Loading states exist.
Query params persist.
Tutor cards bind from data helper/CPT.
Pricing calculator works without reload.
No real external payment/WA/SMS call is made.
No release was run.
```

---

## 18. Codex Patch Strategy

When asked to fix logic:

```text
1. Inspect actual files first.
2. Identify the smallest wrong area.
3. Do not rewrite unrelated screens.
4. Patch helper/component/template in the correct layer.
5. Keep data abstraction.
6. Test locally if runtime exists.
7. Report exactly what changed.
```

Layering rule:

```text
Route problem → page template / rewrite / WP page setup
Data problem → inc/mgk-demo-data.php, CPT query, helper
Button problem → component/button.php or CTA helper
Filter problem → mgk-filters.js + PHP filter helper
Pricing problem → inc/mgk-pricing.php + mgk-pricing.js
State problem → template-parts/states/*
Visual problem → CSS section file + component markup
```

---

## 19. What Codex Must Not Do

```text
Do not copy the full wireframe HTML into PHP.
Do not render .pin, .annotation, .state-chip, .screen-header, .bp-label, .canvas, .device.
Do not hardcode 20 tutor cards inside page templates.
Do not call live PayNow/Stripe/WhatsApp/SMS without credentials and user approval.
Do not require login before public search/listing/profile.
Do not expose private data.
Do not invent unsupported release flags.
Do not change release flow from full package to theme zip.
Do not release unless explicitly asked.
```

---

## 20. Final Definition of Done for Current Batch 1

Batch 1 is acceptable when:

```text
S01-S06 routes exist and load.
UI matches wireframe intent.
Header/footer are consistent.
Search from S01 reaches S02 with params.
S02 filters persist in URL.
S03 profile uses data and handles invalid slug.
S04 subject cards route to listing.
S05 tabs/FAQ work.
S06 calculator works and routes to listing.
Reusable buttons/CTAs exist.
Reusable states exist.
Demo/CPT data layer exists.
Tracking stubs exist.
Local runtime works.
PHP lint passes.
No release was run unless explicitly requested.
```
