# Homepage article update

This update adds an editable article above the homepage footer, styled with a wide, rounded white card, large blue headings, a blue side accent and a keyboard-accessible scroll area. A soft bottom fade indicates more content below and disappears at the end of the article.

## Install on the existing server

1. Back up the six application files listed below if they already exist on the server.
2. Upload `hotspot-homepage-seo-update.zip` through cPanel File Manager and extract it in `/home3/satellit/hotspotbillingsystem.co.ke` (the folder containing `artisan`). Allow the matching application files to be replaced. Keep the folder structure from the ZIP.
3. In the server terminal, run:

   ```bash
   cd /home3/satellit/hotspotbillingsystem.co.ke
   php artisan view:clear
   ```

4. Sign in as an admin and open **Homepage Content** in the dashboard, or visit `https://hotspotbillingsystem.co.ke/admin/homepage-content`.
5. Under **Homepage SEO Article**, enter the article title and content, then click **Save Homepage Content**.
6. Click **View Published Article** to see the saved section on the homepage.

No database migration, database seeding, dependency installation or frontend build is required. The ZIP contains no credentials or database records. If the server uses an authoritative Composer class map, also run `composer dump-autoload --optimize` so the new `App\Support\HomepageArticle` class can be loaded.

## Editing

- The editor supports headings, paragraphs, bold and italic text, lists, blockquotes and links.
- Saving publishes the changes immediately. The new section appears only after article text is saved.
- Clear the article and save to hide the section.
- Existing hero text, images and other homepage fields remain editable in the same form.
- The full saved article is rendered in the initial page HTML. The scroll area does not load additional text dynamically.
- Pasted scripts, embeds, event handlers and custom styling are removed. The article title uses H2; pasted H1/H2 headings in the article body become H3.
- The editor reuses the site's TinyMCE CDN. If the toolbar cannot load, the textarea still accepts plain text or basic HTML.

## Application files in the ZIP

- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/Admin/HomepageContentController.php`
- `app/Support/HomepageArticle.php` (new)
- `resources/views/home.blade.php`
- `resources/views/admin/homepage/edit.blade.php`
- `resources/views/layouts/dashboard.blade.php`

## Validation

`php artisan test`: 9 tests passed, 45 assertions. Covers publishing, updating, empty-content hiding, authorization, title and size validation, safe rich-text output, plain-text line breaks and existing homepage content.

PHP formatting checks and `git diff --check` passed. Browser visual inspection was unavailable because no connected browser was exposed in the workspace.
