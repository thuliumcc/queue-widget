Thulium Queue Widget
====================

Widget do umieszczenia na stronie www, współpracuje z kolejkami systemu [Thulium Call Center](http://callcenter.pl/) pokazując:
 * liczba osób oczekujących w kolejce 
 * przewidywany czas oczekiwania na połączenie z konsultantem

Instalacja
------------

Instalacja polega na skopiowaniu plików z katalogu *widget* oraz *php* do lokalizacji na serwerze.

Widget składa się z dwóch części:
 - **klienckiej** - służącej do umieszczenia na stronie www i wyświetlającej dane o kolejce.
 
 Na stronie należy umieścić element *div* o strukturze:
 ```html
 <div class="thulium-queue-widget" data-queue-id="2" data-interval="6" data-url="http://host/queue-widget/php/queue-widget.php">
     <div class="queue-widget-logo"></div>
     <div class="queue-widget-row">
         <div class="queue-label-title queue-title">Kolejka</div>
         <div class="queue-label queue-value">N/A</div>
     </div>
     <div class="queue-widget-row">
         <div class="queue-approx-wait-title queue-title">Czas oczekiwania</div>
         <div class="queue-approx-wait queue-value">N/A</div>
     </div>
     <div class="queue-widget-row">
         <div class="queue-waiting-count-title queue-title">Oczekujących</div>
         <div class="queue-waiting-count queue-value">N/A</div>
     </div>
 </div>
 ```
 *Struktura może zostać zmodyfikowana należy zachować jedynie główny element div oraz jego atrybut class. Wymagane są również elementy z atrybutami class zawierającymi queue-label, queue-approx-wait, queue-waiting-count.*
 
 Oraz dodać odwołanie do kodu JavaScript oraz styli widgetu:
 
 ```html
<script type="text/javascript" src="../widget/queue-widget.js"></script>
<link rel="stylesheet" href="../widget/queue-widget.css" type="text/css"/>
 ```
*Podane ścieżki mogą wymagać dostosowania w zależności od położenia plików na serwerze.*

*Widget nie wymaga dodatkowych zależności (bibliotek JS), działa pod wszystkimi popularnymi przeglądarkami.*
  
 - **serwerowej** - zapewniającą bezpośrednią komunikację z systemem Thulium.
 
Część serwerowa wymaga PHP (5.3 lub wyższego) oraz opcjonalnie biblioteki [memcache](http://www.memcached.org/).
 
Oprócz skopiowania nie jest wymagana dodatkowa instalacja.


Konfiguracja
------------

Opcje widgetu definiuje się poprzez atrybuty *data* umieszczonego na stronie elementu *div*:
- `data-queue-id` - identyfikator kolejki dla której mają być wyświetlane informacje
- `data-interval` - częstotliwość (w sekundach) odświeżania informacji
- `data-url` - adres części serwerowej (pliku **queue-widget.php**) widgetu

Dodatkowo należy ustawić opcje połączenia Twojego serwera z systemem Thulium (plik **queue-widget.config.php**):
- `api_url` - adres api systemu Thulium
- `user` - nazwa użytkownika 
- `password` - hasło użytkownika 
   (Aby uzyskać nazwę użytkownika i hasło należy skontaktować się z serwisem Thulium)  
- `permitted_queue_ids` - lista identyfikatorów kolejek dla których widget może pobierać dane. W przypadku gdy na stronie są umieszczone widgety dla kilku kolejek powinny być tu umieszczone wszystkie ich identyfikatory (wartości z pola `data-queue-id`). 
- `queue_names` - wyświetlane nazwy kolejek w postaci **identyfikator kolejki** => **nazwa do wyświetlenia**
- `cache` - opcje związane z pamięcią podręczną danych kolejek. Pamięć podręczna może być użyta w celu zmniejszenia ruchu pomiędzy Twoim serwerem a systemem Thulium.

Dostępne opcje pamięci podręcznej:
- `enabled` - czy pamięć podręczna powinna być używana
- `clean_interval` - co ile sekund należy odświeżyć dane znajdujące się w pamięci podręcznej (domyślnie 30) 
- `class` - nazwa klasy PHP implementująca pamięć podręczną. Obecnie zdefiniowana jest jedynie klasa współpracująca z biblioteką [memcache](http://www.memcached.org/). W przypadku użycia klasy użytkownika wymagana jest konwencja aby klasa znajdowała się w pliku .php o nazwie zgodnej z nazwą klasy.  
- `class_path` - ścieżka bezwzględna do katalogu gdzie znajduje się plik z klasą podaną w `class` 
- `server` - serwer memcache    
- `port` - port memcache  
        
Przykładowa konfiguracja
------------------------

Zawartość pliku **queue-widget.config.php**:

```php
$configuration = array(
    'api_url' => 'http://twoja_domena.callcenter.pl/api',
    'user' => 'user',
    'password' => 'password',
    'permitted_queue_ids' => array(1, 2),
    'queue_names' => array(
        1 => 'Infolinia',
        2 => 'Wsparcie Techniczne'
    ),
    'cache' => array(
        'enabled' => true,
        'clean_interval' => 30,
        'class' => 'MemcacheCache',
        'class_path' => __DIR__,
        'server' => 'localhost',
        'port' => '11211'
    )
);
```
