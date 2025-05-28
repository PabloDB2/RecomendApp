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

    public function testPeliculasOrdenadasPorPuntuacionDesc()
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
            $prev = $peliculas[$i - 1]['vote_average'];
            $curr = $peliculas[$i]['vote_average'];
            $this->assertTrue(
                $prev >= $curr || abs($prev - $curr) < 0.1
            );
        }
    }
}
