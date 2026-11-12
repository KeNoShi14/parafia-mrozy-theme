# Motyw WordPress: Parafia Mrozy

Motyw WordPress dla Parafii Rzymskokatolickiej pw. św. Teresy od Dzieciątka Jezus w Mrozach.

---

## Instalacja

1. Wgraj folder `parafia-mrozy` do katalogu `/wp-content/themes/`  
   (lub użyj **Wygląd → Motywy → Dodaj nowy → Wyślij motyw** i wgraj plik `.zip`)
2. Aktywuj motyw w **Wygląd → Motywy**
3. Po aktywacji przejdź do **Ustawienia → Bezpośrednie odnośniki** i kliknij „Zapisz zmiany" (odświeża reguły URL dla niestandardowych typów postów)

---

## Konfiguracja strony głównej

Aby strona główna wyświetlała layout z makiety:

1. Utwórz nową stronę (np. „Strona główna") — treść może być pusta
2. Idź do **Ustawienia → Czytanie** → ustaw „Strona główna wyświetla" na „Strona statyczna" → wybierz utworzoną stronę
3. Motyw automatycznie wykryje stronę główną i załaduje szablon `front-page.php`

---

## Sekcje strony głównej

Strona główna składa się z następujących sekcji (w kolejności od góry):

| Sekcja | Skąd pochodzi treść |
|--------|---------------------|
| **Slider Hero** | Wpisy z kategorii `aktualnosci` oznaczone jako hero (meta `_is_hero_slide = 1`) |
| **Adwent — Droga do Betlejem** | Panel → Adwent (widoczne tylko w Adwencie i okresie Bożego Narodzenia) |
| **Aktualności parafialne** | Wpisy z kategorii `aktualnosci` (6 najnowszych) |
| **Ogłoszenia parafialne** | Typ postu `ogloszenia` (3 najnowsze) |
| **Intencje Mszy Św.** | Typ postu `intencje` (3 najnowsze) |
| **Vatican News** | Typ postu `vatican_news` (3 najnowsze) |
| **Info panel** | Dane z Customizera (kancelaria + porządek mszy) |
| **Cytat** | Statyczny tekst (edytowalny w `front-page.php`) |
| **Galeria** | Wpisy z kategorii `galeria` (3 najnowsze) |

---

## Adwent — „Droga do Betlejem”

Na stronie głównej, pod sliderem, w czasie Adwentu pojawia się sekcja z wędrówką Maryi i Józefa do Betlejem.

- **Kiedy się pokazuje:** automatycznie od I niedzieli Adwentu (data liczona co roku sama) do terminu wybranego w panelu (domyślnie 6 stycznia). Od 25 grudnia widać grotę i Świętą Rodzinę we troje.
- **Edycja treści:** menu **Adwent** w panelu WordPress. Można tam zmienić:
  - tytuł i opis każdego dnia, Ewangelię, cytat, „Ślad historii” i kilometry,
  - treść dnia Bożego Narodzenia, ramkę o niedzieli Gaudete, nagłówki tygodni, nazwy przystanków na mapie, nagłówek sekcji i przycisk.
- **Podgląd dowolnego dnia:** w panelu przy każdym dniu jest link „Podgląd tego dnia na stronie” (działa tylko dla zalogowanych redaktorów, adres `/?adwent_podglad=RRRR-MM-DD`).
- **Tryb testowy:** opcja w panelu — sekcję widzą wtedy tylko zalogowani redaktorzy.
- Pliki: `inc/adwent.php` (logika, sekcja, panel), `inc/adwent-dane.php` (domyślne treści), `css/adwent.css`.

---

## Niestandardowe typy postów

### Aktualności (Posts + kategoria)
- Używaj standardowych Wpisów z kategorią `aktualnosci`
- Aby wpis pojawił się w sliderze hero: edytuj wpis → panel boczny „Ustawienia slajdera hero" → zaznacz checkbox

### Ogłoszenia parafialne
- Administracja → **Ogłoszenia parafialne** → Dodaj nowe

### Intencje Mszy Św.
- Administracja → **Intencje Mszy Św.** → Dodaj nowe
- Aby dodać przycisk PDF: dodaj meta pole `_pdf_url` z URL do pliku PDF

### Galerie zdjęć
- Administracja → **Galerie** → **Dodaj galerię**
- Wpisz nazwę wydarzenia, przeciągnij zdjęcia z komputera (albo wybierz je z biblioteki mediów), ustaw kolejność przeciągając miniatury, gwiazdką wybierz okładkę, uzupełnij datę wydarzenia i krótki opis → **Opublikuj**
- Galerie to wpisy w kategorii `galeria` — kategoria jest ustawiana automatycznie
- Galerie są sortowane wg **daty wydarzenia** (strona „Galerie”, filtr lat, strona główna, nawigacja „poprzednia / następna”)
- Starsze galerie (zdjęcia wstawione blokiem w treści wpisu) wyświetlają się jak dotąd; po otwarciu w kokpicie zdjęcia pojawią się w nowym panelu i po zapisaniu przejdą na nowy format

### Zdjęcia → WebP
- Każde wgrywane zdjęcie JPG / PNG / HEIC jest od razu zamieniane na WebP (jakość 82, najdłuższy bok maks. 1920 px, obrót wg EXIF) — w bibliotece mediów zostaje tylko plik `.webp`
- Ustawienia: stałe `PARAFIA_IMG_QUALITY` i `PARAFIA_IMG_MAX_WIDTH` w `inc/webp.php`; konwersję można wyłączyć filtrem `parafia_mrozy_webp_wlaczone`

### Vatican News
- Administracja → **Vatican News** → Dodaj nowe

---

## Customizer (Wygląd → Dostosuj)

### Dane parafii
- Adres, telefon, e-mail
- Numer konta bankowego i nazwa banku
- URL do mapy Google (src iframe)

### Porządek Mszy Św.
- Godziny mszy w dni powszednie
- Godziny mszy w niedzielę
- Godziny spowiedzi
- Godziny różańca

### Kolory motywu
- Kolor złoty (akcent)
- Kolor ciemnego tła

---

## Nawigacja

Ustaw menu w **Wygląd → Menu**:
- Utwórz menu i przypisz do lokalizacji **„Menu główne"**
- Zalecane pozycje: Strona główna, Aktualności, O parafii, Duszpasterstwo, Grupy, Wsparcie, Kontakt

---

## Logo

Dodaj logo parafii w **Wygląd → Dostosuj → Tożsamość witryny → Logo**.  
Zalecany rozmiar: min. 80×80 px, najlepiej format PNG z przezroczystym tłem.

---

## Kategorie wpisów (do założenia)

Po instalacji utwórz następujące kategorie wpisów:
- `aktualnosci` — Aktualności parafialne
- `galeria` — Galeria zdjęć
- `fotorelacja` — Fotorelacje
- `zaproszenie` — Zaproszenia

---

## Wymagania

- WordPress 6.0+
- PHP 7.4+
