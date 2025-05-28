<?php
use PHPUnit\Framework\TestCase;

class PeliculaTest extends TestCase
{
    public function testPeliculasApiDevuelvePeliculas()
    {
        $api_key = '6bfc367c7d8bc2e83a9e4f5ced5a2bd4';
        $url = "https://api.themoviedb.org/3/discover/movie?api_key=$api_key&language=es-ES&page=1";
        $response = @file_get_contents($url);
        $this->assertNotFalse($response);
        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('results', $data);
        $this->assertNotEmpty($data['results']);
    }

    public function testPeliculasOrdenadasPorPuntuacionDescendente()
    {
        $api_key = '6bfc367c7d8bc2e83a9e4f5ced5a2bd4';
        $url = "https://api.themoviedb.org/3/discover/movie?api_key=$api_key&language=es-ES&sort_by=vote_average.desc&vote_count.gte=100&page=1";
        $response = @file_get_contents($url);
        $this->assertNotFalse($response);
        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('results', $data);
        $peliculas = $data['results'];
        $this->assertGreaterThan(1, count($peliculas));
        for ($i = 1; $i < count($peliculas); $i++) {
            $previa = $peliculas[$i - 1]['vote_average'];
            $posicion = $peliculas[$i]['vote_average'];
            $this->assertTrue(
                $previa >= $posicion || abs($previa - $posicion) < 0.1
            );
        }
    }

    public function testAnadirFavoritoGuardaEnSesion()
    {
        $_SESSION = [];
        $idPelicula = 123;
        // Simula el click en el botón de añadir a favoritos
        if (!isset($_SESSION['favoritos'])) {
            $_SESSION['favoritos'] = [];
        }
        if (!in_array($idPelicula, $_SESSION['favoritos'])) {
            $_SESSION['favoritos'][] = $idPelicula;
        }
        $this->assertContains($idPelicula, $_SESSION['favoritos']);
    }

    //  construir la url de imagen de una película
    public function testConstruirUrlImagenPelicula()
    {
        $poster_path = '/abc123.jpg';
        $tamaño = 'w780'; // utilizado asi para los posters de peliculas
        $url = "https://image.tmdb.org/t/p/{$tamaño}{$poster_path}";
        $this->assertEquals('https://image.tmdb.org/t/p/w780/abc123.jpg', $url);
    }

    // filtrar películas por año
    public function testFiltrarPeliculasPorAnio()
    {
        $peliculas = [
            ['title' => 'Peli1', 'release_date' => '2022-01-01'],
            ['title' => 'Peli2', 'release_date' => '2023-05-10'],
            ['title' => 'Peli3', 'release_date' => '2022-11-20'],
        ];
        $anio = '2022';
        $filtradas = array_filter($peliculas, function($peli) use ($anio) {
            return strpos($peli['release_date'], $anio) === 0;
        });
        $this->assertCount(2, $filtradas);
    }
}
