<?php

require_once(__DIR__ . '/../../app/model/Usuario.php');
require_once(__DIR__ . '/../../config/dbConnection.php');

use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase
{
    private $testNombre;
    private $testEmail;
    private $testContraseña;

    protected function setUp(): void
    {
        $this->testNombre = 'nombreTest' . uniqid();
        $this->testEmail = 'emailTest' . uniqid() . '@gmail.com';
        $this->testContraseña = '123456';
    }

    protected function tearDown(): void
    {
        $usuarioBD = Usuario::getUserByName($this->testNombre);
        if ($usuarioBD !== null) {
            $usuarioBD->delete();
        }
    }

public function testCrearUsuario()
{
    $usuario = new Usuario();
    $usuario->setNombreUsuario($this->testNombre);
    $usuario->setEmail($this->testEmail);
    $usuario->setContraseña(password_hash($this->testContraseña, PASSWORD_DEFAULT)); // HASHEAR antes de guardar
    $usuario->create();

    $usuarioBD = Usuario::getUserByName($this->testNombre);
    $this->assertNotNull($usuarioBD, 'Usuario encontrado');
    $this->assertEquals($this->testNombre, $usuarioBD->getNombreUsuario());
    $this->assertEquals($this->testEmail, $usuarioBD->getEmail());
    $this->assertNotEmpty($usuarioBD->getContraseña(), 'Contraseña no vacía');
}


    public function testContraseñaSeHashea()
{
    $hashedPassword = password_hash($this->testContraseña, PASSWORD_DEFAULT);

    $usuario = new Usuario();
    $usuario->setNombreUsuario($this->testNombre);
    $usuario->setEmail($this->testEmail);
    $usuario->setContraseña($hashedPassword);
    $usuario->create();

    $usuarioBD = Usuario::getUserByName($this->testNombre);

    $this->assertNotNull($usuarioBD, 'Usuario encontrado');
    $this->assertNotEquals($this->testContraseña, $usuarioBD->getContraseña(), 'La contraseña guardada no debe ser igual al texto plano');
    $this->assertTrue(password_verify($this->testContraseña, $usuarioBD->getContraseña()), 'La contraseña debe coincidir con el hash guardado');
}

public function testCrearUsuarioNombreInvalido()
{
    $usuario = new Usuario();
    $usuario->setNombreUsuario('');
    $usuario->setEmail($this->testEmail);
    $usuario->setContraseña(password_hash($this->testContraseña, PASSWORD_DEFAULT));
    
    $result = $usuario->create();
    $this->assertFalse($result, 'El nombre de usuario no puede estar vacío');
    
    $usuarioBD = Usuario::getUserByEmail($this->testEmail);
    $this->assertNull($usuarioBD, 'Usuario no existe');
}

public function testCrearUsuarioEmailInvalido()
{
    $usuario = new Usuario();
    $usuario->setNombreUsuario($this->testNombre);
    $usuario->setEmail('emailinvalido');
    $usuario->setContraseña(password_hash($this->testContraseña, PASSWORD_DEFAULT));
    
    $result = $usuario->create();
    $this->assertFalse($result, 'Email no válido');
    
    $usuarioBD = Usuario::getUserByName($this->testNombre);
    $this->assertNull($usuarioBD, 'Usuario no existe');
}
}
