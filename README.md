# Mat‑chatbot

Lettvekts PHP‑chatbot for oppskrifter (ThemealDB) — enkel MVC‑struktur med modeller, services og views. Designet for kjøring i XAMPP/Apache eller med innebygd PHP‑server.

Kort om prosjektet
- Henter oppskrifter fra TheMealDB.
- Enkel brukersystem (register/login) og logging av spørringer.
- Chat‑UI med kort (cards) og detaljvisning, historikkside som matcher chat‑design.
- Struktur: app/{controllers,models,services,view_helpers,views}, public (front), lib (utils).

Krav
- PHP 7.4+ (PHP 8 anbefalt)
- MySQL (XAMPP levert)
- cURL enabled (fallback til file_get_contents finnes)
- (Valgfritt) Composer for autoloading

Rask oppstart (XAMPP på macOS)
1. Plasser prosjektet i XAMPP htdocs, f.eks:
   - /Applications/XAMPP/xamppfiles/htdocs/is115/chatbot

2. Opprett config:
   - Kopier `app/configEXAMPLE.php` → `app/config.php`
   - Rediger DB‑innstillinger (host, dbname, user, pass).

3. Importer databasen:
   - Start MySQL i XAMPP (via XAMPP Control Panel).
   - Import SQL:
     ```
     mysql -u root -p < [chatbotSql.sql](http://_vscodecontentref_/0)
     ```
     (tilpass bruker/passord hvis ikke root uten passord)

4. Åpne i nettleser:
   - http://localhost/is115/chatbot/public/
   - Eller kjør innebygd server (for utvikling):
     ```
     php -S localhost:8000 -t public
     ```

Konfigurasjon
- `app/config.php` — DB + app‑innstillinger. Sørg for riktige credentials.
- `public/index.php` er front controller / router.

Viktige filer/mapper
- app/controllers — ChatbotController, AuthController, HistoryController
- app/models — RecipeModel, QueryLogModel, UserModel
- app/services — ChatbotService (chat/flow logic)
- app/view_helpers — ResponseRenderer (render helpers)
- app/lib — HttpClient, Database
- app/views — chatbot.php, history.php, login.php, register.php, header/footer
- public — index.php (front), css/, images/

Hvordan bruke chatten
- Logg inn eller registrer en bruker.
- I chatten kan du skrive kommandoer som:
  - `kategori` — viser kategorier
  - `tilfeldig` eller `random` — viser tilfeldig rett (detail)
  - `fra Norway` eller `fra Italy` — viser kortliste fra område
  - skriv et nummer for å velge et element i siste kortliste
- Historikk lagrer kort oppsummering + type; klikk en rad for å se full respons.

Feilsøking
- Fatal errors ved konstruktører? Sjekk at `app/lib/HttpClient.php` og `app/models/RecipeModel.php` kreves riktig og at `new RecipeModel()` får riktig type (HttpClient eller ingen param).
- Hvis layout oppfører seg uventet: tøm nettleser‑cache eller sjekk `public/css/style.css` responsiv regler.
- Sjekk `error_log()` i `app/views/header.php` og PHP‑error log hvis sesssion/headers feiler.

Lisens
- MIT (se LICENSE).

Kontakt
- Ingen videre kontaktinfo i README — se prosjektets root for detaljer.