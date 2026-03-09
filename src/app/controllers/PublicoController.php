<?php
require_once APP_PATH . '/controllers/Controller.php';

class PublicoController extends Controller {

    public function home(): void {
        $this->render('publico/home', [
            'titulo'      => 'Inicio',
            'clase_body'  => 'pagina-home',
        ]);
    }

    public function programa(): void {
        $this->render('publico/programa', ['titulo' => 'El Programa']);
    }

    public function requisitos(): void {
        $this->render('publico/requisitos', ['titulo' => 'Requisitos']);
    }

    public function galeria(): void {
        $this->render('publico/galeria', ['titulo' => 'Galería']);
    }

    public function impacto(): void {
        $this->render('publico/impacto', ['titulo' => 'Impacto']);
    }

    public function contacto(): void {
        $this->render('publico/contacto', ['titulo' => 'Contacto']);
    }
}
