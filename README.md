# laravel-schemaspy-meta

## Installation

```
composer require --dev mya-zaki/laravel-schemaspy-meta
```

## Generate XML

```
php artisan schemaspy-meta:generate
```

### arguments

`namespace`: `string` | App  
The namespace of Eloquent Models. e.g. App\\\\Models, 'App\Models'

### options

`--xmlFile`: `string` | schemaspy-meta.xml
Output path of schema xml.

`--excludeClass`: `array` (optional)
The specified classes are ignored.

```
e.g.
--excludeClass Foo --excludeClass Bar
```

## Configration

Run the command `$ php artisan vendor:publish`  
The file schemaspy_meta.php will then be copied to *app/config*.  

Exclude subclasses that inherit the specified superclasses.  
