# Top movies php
A feladat egy filmes adatbázis létrehozása az TMDB “top rated movies” listájának első
210 helyezettének adataival, amit a themoviedb.org api használatával kell
összegyűjteni. Ehhez kell majd API clientet készítened, valamint letárolnod a szükséges
adatokat.

A próbafeladatot bármely általad kedvelt PHP framework, vagy library használatával
végezheted ( ajánlott Lumen, vagy Laravel mivel azt használunk elsősorban, de
természetesen nem probléma ha nem ebben készül).

A kész feladatod egy publikus git repositoryba pusholva oszd meg majd velünk kérlek.

Az API dokumentációját az alábbi linken érheted el [https://developers.themoviedb.org/3](https://developers.themoviedb.org/3)

Legyen megoldva az adatok iterált frissítése is amit cron scheduler beállításával
tudnánk bizonyos időközönként futtatni.

Az adatokból MySQL adatbázist kell készíteni. Törekedj a típusok helyes
megválasztására, a redundancia elkerülésére, és a performancia szem előtt tartására.

Szükséges adatok amiknek szerepelnie kell az általad készített adatbázisban:

- title
- length
- genre(s)
- release date
- overview
- poster url
- tmdb id
- tmdb vote average
- tmbd vote count
- tmdb url
- director(s) name
- director(s) tmdb id
- directors biography
- directors date of birth

Ha megoldható az api cliented szervezd ki külön composer packagebe, és a
composeren keresztül integráld az alkalmazásba. (Nem szükséges packgagist
használata, elegendő ha ez egy lokális csomag)

Nem kritérium, de előnyös lenne ha a megoldásod tartalmazna teszteket, hogy
biztosítsd a kódod megfelelő működését.
