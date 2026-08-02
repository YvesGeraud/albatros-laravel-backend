<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Combo;
use App\Models\Event;
use App\Models\EventMedia;
use App\Models\Product;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@albatrostlaxcala.com'],
            ['name' => 'Admin Albatros', 'password' => bcrypt('albatros2026')]
        );

        $sonido = Category::create([
            'name' => 'Sonido',
            'slug' => 'sonido',
            'description' => 'Equipo de audio profesional para todo tipo de eventos.',
            'icon' => 'bi-soundwave',
            'sort_order' => 1,
        ]);

        $iluminacion = Category::create([
            'name' => 'Iluminación',
            'slug' => 'iluminacion',
            'description' => 'Luces y efectos para ambientar la fiesta.',
            'icon' => 'bi-lightbulb',
            'sort_order' => 2,
        ]);

        $pista = Category::create([
            'name' => 'Pista de baile',
            'slug' => 'pista-de-baile',
            'description' => 'Pistas de baile LED y tradicionales.',
            'icon' => 'bi-grid-3x3-gap',
            'sort_order' => 3,
        ]);

        $entretenimiento = Category::create([
            'name' => 'Bailarines y entretenimiento',
            'slug' => 'bailarines-y-entretenimiento',
            'description' => 'Bailarines, animadores y shows en vivo.',
            'icon' => 'bi-stars',
            'sort_order' => 4,
        ]);

        $bocina = Product::create([
            'category_id' => $sonido->id,
            'name' => 'Bocina activa 15"',
            'slug' => 'bocina-activa-15',
            'description' => 'Bocina activa de alta potencia, ideal para interiores y exteriores.',
            'price' => 800,
            'sort_order' => 1,
        ]);

        $subwoofer = Product::create([
            'category_id' => $sonido->id,
            'name' => 'Subwoofer 18"',
            'slug' => 'subwoofer-18',
            'description' => 'Refuerzo de graves para pistas grandes.',
            'price' => 1200,
            'sort_order' => 2,
        ]);

        Product::create([
            'category_id' => $sonido->id,
            'name' => 'Consola de audio digital',
            'slug' => 'consola-audio-digital',
            'description' => 'Mezcla profesional para DJ y micrófonos en vivo.',
            'price' => 1500,
            'sort_order' => 3,
        ]);

        $cabezaMovil = Product::create([
            'category_id' => $iluminacion->id,
            'name' => 'Cabeza móvil robótica',
            'slug' => 'cabeza-movil-robotica',
            'description' => 'Luz robótica con movimiento y colores dinámicos.',
            'price' => 600,
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id' => $iluminacion->id,
            'name' => 'Bola disco LED',
            'slug' => 'bola-disco-led',
            'description' => 'La clásica bola disco con iluminación LED moderna.',
            'price' => 400,
            'sort_order' => 2,
        ]);

        Product::create([
            'category_id' => $iluminacion->id,
            'name' => 'Máquina de humo',
            'slug' => 'maquina-de-humo',
            'description' => 'Efecto de humo para resaltar la iluminación.',
            'price' => 500,
            'sort_order' => 3,
        ]);

        $pistaLed = Product::create([
            'category_id' => $pista->id,
            'name' => 'Pista de baile LED 6x6m',
            'slug' => 'pista-de-baile-led-6x6',
            'description' => 'Pista de baile iluminada con LEDs RGB.',
            'price' => 4500,
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id' => $pista->id,
            'name' => 'Pista de baile clásica 5x5m',
            'slug' => 'pista-de-baile-clasica-5x5',
            'description' => 'Pista de baile de madera, montaje tradicional.',
            'price' => 3000,
            'sort_order' => 2,
        ]);

        $bailarin = Product::create([
            'category_id' => $entretenimiento->id,
            'name' => 'Bailarín(a) go-go',
            'slug' => 'bailarin-go-go',
            'description' => 'Bailarín(a) profesional para animar la pista.',
            'price' => 1000,
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id' => $entretenimiento->id,
            'name' => 'Animador / MC',
            'slug' => 'animador-mc',
            'description' => 'Maestro de ceremonias para conducir el evento.',
            'price' => 1800,
            'sort_order' => 2,
        ]);

        $comboFiesta = Combo::create([
            'name' => 'Combo Fiesta Neón',
            'slug' => 'combo-fiesta-neon',
            'description' => 'Sonido, iluminación y pista LED para una fiesta completa.',
            'price' => 9500,
            'sort_order' => 1,
        ]);
        $comboFiesta->products()->attach([
            $bocina->id => ['quantity' => 2],
            $subwoofer->id => ['quantity' => 1],
            $cabezaMovil->id => ['quantity' => 2],
            $pistaLed->id => ['quantity' => 1],
        ]);

        $comboShow = Combo::create([
            'name' => 'Combo Show en Vivo',
            'slug' => 'combo-show-en-vivo',
            'description' => 'Sonido, iluminación y bailarines para un show inolvidable.',
            'price' => 12000,
            'sort_order' => 2,
        ]);
        $comboShow->products()->attach([
            $bocina->id => ['quantity' => 2],
            $cabezaMovil->id => ['quantity' => 3],
            $bailarin->id => ['quantity' => 2],
        ]);

        $eventoZocalo = Event::create([
            'title' => 'Fiesta Patronal Tlaxcala Centro',
            'slug' => 'fiesta-patronal-tlaxcala-centro',
            'description' => 'Presentación en la plaza principal de Tlaxcala.',
            'venue_name' => 'Plaza de la Constitución',
            'address' => 'Centro, Tlaxcala de Xicohténcatl, Tlaxcala',
            'latitude' => 19.3182,
            'longitude' => -98.2375,
            'event_date' => now()->subMonths(2),
            'is_live' => false,
        ]);
        EventMedia::create([
            'event_id' => $eventoZocalo->id,
            'type' => 'youtube_video',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'external_id' => 'dQw4w9WgXcQ',
            'caption' => 'Resumen del evento',
            'sort_order' => 1,
        ]);

        $eventoApizaco = Event::create([
            'title' => 'Feria de Apizaco',
            'slug' => 'feria-de-apizaco',
            'description' => 'Show musical en la feria anual de Apizaco.',
            'venue_name' => 'Feria Apizaco',
            'address' => 'Apizaco, Tlaxcala',
            'latitude' => 19.4126,
            'longitude' => -98.1445,
            'event_date' => now()->subMonth(),
            'is_live' => false,
        ]);
        EventMedia::create([
            'event_id' => $eventoApizaco->id,
            'type' => 'facebook_post',
            'url' => 'https://www.facebook.com/albatrostlaxcala/posts/example',
            'caption' => 'Fotos del evento',
            'sort_order' => 1,
        ]);

        $eventoHuamantla = Event::create([
            'title' => 'Boda en Huamantla',
            'slug' => 'boda-en-huamantla',
            'description' => 'Presentación en vivo ahora mismo desde Huamantla.',
            'venue_name' => 'Jardín Los Pinos',
            'address' => 'Huamantla, Tlaxcala',
            'latitude' => 19.3167,
            'longitude' => -97.9167,
            'event_date' => now(),
            'is_live' => true,
        ]);
        EventMedia::create([
            'event_id' => $eventoHuamantla->id,
            'type' => 'youtube_live',
            'url' => 'https://www.youtube.com/watch?v=5qap5aO4i9A',
            'external_id' => '5qap5aO4i9A',
            'caption' => 'Transmisión en vivo',
            'sort_order' => 1,
        ]);

        Event::create([
            'title' => 'Festival Cultural Chiautempan',
            'slug' => 'festival-cultural-chiautempan',
            'description' => 'Próxima presentación en el festival cultural.',
            'venue_name' => 'Plaza Chiautempan',
            'address' => 'Chiautempan, Tlaxcala',
            'latitude' => 19.3167,
            'longitude' => -98.1833,
            'event_date' => now()->addWeeks(3),
            'is_live' => false,
        ]);

        Testimonial::create([
            'customer_name' => 'Mariana y Luis',
            'event_type' => 'Boda',
            'content' => 'Albatros hizo que nuestra boda fuera inolvidable. El sonido y la iluminación estuvieron perfectos toda la noche, y los invitados no dejaron de bailar.',
            'rating' => 5,
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'customer_name' => 'Familia Hernández',
            'event_type' => 'XV años',
            'content' => 'Muy profesionales desde la cotización hasta el evento. La pista de baile LED fue el éxito de la fiesta.',
            'rating' => 5,
            'sort_order' => 2,
        ]);

        Testimonial::create([
            'customer_name' => 'Constructora Tlaxcala S.A.',
            'event_type' => 'Evento empresarial',
            'content' => 'Contratamos el combo de sonido e iluminación para nuestra posada anual. Excelente servicio y puntualidad.',
            'rating' => 4,
            'sort_order' => 3,
        ]);
    }
}
