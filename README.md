# CMS New

Nový základ CMS nad Nette pro PHP 8.4/8.5.

## Lokální start

1. `composer install`
2. `npm install`
3. Zkopírovat `.env.example` na `.env.local` a upravit připojení k DB.
4. Importovat `database/schema.sql` do databáze `cmd-new`.
5. `npm run dev`
6. `php -S 127.0.0.1:8000 -t www`

Admin modul je na `/admin`. Debug bypass funguje přes cookie `nette-debug=michal` nebo povolené IP v `config/local.neon`.

## Databáze

Schéma je navržené pro MariaDB/MySQL:

- `project_settings` drží základ projektu, kontakty, měřicí kódy, statické texty a OG obrázek.
- `media_assets` je centrální knihovna obrázků.
- `media_variants` cacheuje vygenerované AVIF/WebP varianty pro konkrétní rozměry.
- `galleries`, `gallery_items` drží dynamické galerie.
- `content_entries` pokrývají novinky, články a statické stránky.
- `products`, `product_categories`, `product_tags` a vazební tabulky řeší katalog.
- `price_items`, `opening_hours`, `rooms`, `trainings`, `training_registrations` pokrývají ceník, provozní dobu a rezervace.
- `menu_items` generuje navigaci včetně detailů obsahu a kotev.
- `users` podporuje role `admin`, `manager`, `trainer`, `client`.

## Obrázky v Latte

V šablonách používej:

```latte
{if $item->image_id}
    {=cmsPicture($item->image_id, width: 960, height: 540, alt: $item->title, class: 'rounded')}
{/if}
```

Služba vytvoří AVIF, pokud ho server přes GD podporuje, jinak WebP v kvalitě 80.
