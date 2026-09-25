# -*- coding: utf-8 -*-
"""
Sube a la galería del sitio las fotos nuevas de una carpeta local.

Cómo se ordenan las fotos:

    web/
      Oficios/                  <- carpeta = actividad
        foto1.jpg               <- título: "Oficios"
        Panadería/              <- subcarpeta = título de esas fotos
          foto2.jpg
      Devocional/
      Bienestar Misionero/
      ...

Cada foto se comprime (1920 px máximo, sin datos de GPS) y se sube a la
ciudad que diga config.ini. Las que ya subió quedan anotadas en
web/.subidas.json y no se vuelven a subir, aunque las muevas de carpeta.

Uso:
    python subir_galeria.py            sube lo nuevo
    python subir_galeria.py --probar   muestra qué subiría, sin subir nada
"""
import argparse
import configparser
import hashlib
import io
import json
import os
import re
import sys
import unicodedata

import requests
from PIL import Image, ImageOps

AQUI = os.path.dirname(os.path.abspath(__file__))
EXTENSIONES = ('.jpg', '.jpeg', '.png', '.webp')

# Mismas claves que Controller::ACTIVIDADES_GALERIA
ACTIVIDADES = {
    'devocional': 'devocional',
    'aula': 'aula',
    'evangelismo': 'evangelismo',
    'oficios': 'oficios',
    'bienestar misionero': 'bienestar',
    'bienestar': 'bienestar',
    'mantenimiento': 'mantenimiento',
    'eventos': 'eventos',
}


def normalizar(texto):
    sin_tildes = unicodedata.normalize('NFKD', texto).encode('ascii', 'ignore').decode()
    return sin_tildes.strip().lower()


def huella(ruta):
    h = hashlib.sha1()
    with open(ruta, 'rb') as f:
        for bloque in iter(lambda: f.read(1 << 20), b''):
            h.update(bloque)
    return h.hexdigest()


def comprimir(ruta):
    im = ImageOps.exif_transpose(Image.open(ruta)).convert('RGB')
    im.thumbnail((1920, 1920), Image.LANCZOS)
    salida = io.BytesIO()
    # Se guarda sin EXIF: así no viaja la ubicación de donde duermen los misioneros
    im.save(salida, 'JPEG', quality=82, optimize=True)
    return salida.getvalue()


def buscar_fotos(raiz):
    """Devuelve (ruta, actividad, título) de cada foto dentro de las carpetas de actividad."""
    fotos, ignoradas = [], []
    for carpeta in sorted(os.listdir(raiz)):
        ruta_act = os.path.join(raiz, carpeta)
        if not os.path.isdir(ruta_act) or carpeta.startswith('.'):
            continue
        actividad = ACTIVIDADES.get(normalizar(carpeta))
        if not actividad:
            ignoradas.append(carpeta)
            continue
        for dirpath, _, archivos in os.walk(ruta_act):
            relativa = os.path.relpath(dirpath, ruta_act)
            titulo = carpeta if relativa == '.' else relativa.split(os.sep)[0]
            for nombre in sorted(archivos):
                if nombre.lower().endswith(EXTENSIONES):
                    fotos.append((os.path.join(dirpath, nombre), actividad, titulo))
    return fotos, ignoradas


def token_de(html):
    m = re.search(r'name="_token"\s+value="([^"]+)"', html)
    if not m:
        sys.exit('No encontré el token del formulario. ¿Cambió el sitio?')
    return m.group(1)


def iniciar_sesion(s, sitio, email, clave):
    token = token_de(s.get(f'{sitio}/login', timeout=30).text)
    r = s.post(f'{sitio}/login', data={'_token': token, 'email': email, 'password': clave}, timeout=30)
    if '/admin' not in r.url:
        sys.exit('No pude entrar al panel. Revisa el correo y la clave en config.ini.')


def main():
    # La consola de Windows no es UTF-8 por defecto y se cae con tildes y ✓
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')
    parser = argparse.ArgumentParser(description='Sube fotos nuevas a la galería.')
    parser.add_argument('--probar', action='store_true', help='solo muestra qué subiría')
    args = parser.parse_args()

    cfg = configparser.ConfigParser()
    if not cfg.read(os.path.join(AQUI, 'config.ini'), encoding='utf-8'):
        sys.exit('Falta config.ini. Copia config.ejemplo.ini, renómbralo y llénalo.')
    c = cfg['galeria']
    sitio = c['sitio'].rstrip('/')
    raiz = os.path.normpath(os.path.join(AQUI, c['carpeta']))
    sede_id = c['sede_id']

    if not os.path.isdir(raiz):
        sys.exit(f'No existe la carpeta {raiz}')

    registro_ruta = os.path.join(raiz, '.subidas.json')
    registro = {}
    if os.path.exists(registro_ruta):
        with open(registro_ruta, encoding='utf-8') as f:
            registro = json.load(f)

    fotos, ignoradas = buscar_fotos(raiz)
    for carpeta in ignoradas:
        print(f'  ! La carpeta "{carpeta}" no es una actividad conocida; la salto.')

    nuevas = []
    for ruta, actividad, titulo in fotos:
        h = huella(ruta)
        if h not in registro:
            nuevas.append((ruta, actividad, titulo, h))

    if not nuevas:
        print('No hay fotos nuevas.')
        return

    print(f'{len(nuevas)} foto(s) nueva(s):')
    for ruta, actividad, titulo, _ in nuevas:
        print(f'  - {os.path.relpath(ruta, raiz)}  ->  {actividad} / "{titulo}"')
    if args.probar:
        return

    s = requests.Session()
    iniciar_sesion(s, sitio, c['email'], c['clave'])
    token = token_de(s.get(f'{sitio}/admin/galeria', params={'sede': sede_id}, timeout=30).text)

    subidas = 0
    for ruta, actividad, titulo, h in nuevas:
        nombre = os.path.relpath(ruta, raiz)
        try:
            datos = comprimir(ruta)
        except Exception as e:
            print(f'  x {nombre}: no se pudo abrir ({e})')
            continue
        r = s.post(f'{sitio}/admin/galeria', timeout=120, data={
            '_token': token, 'accion': 'subir', 'ajax': '1', 'tipo': 'foto',
            'sede_id': sede_id, 'titulo': titulo, 'actividad': actividad,
        }, files={'archivo': (os.path.splitext(os.path.basename(ruta))[0] + '.jpg', datos, 'image/jpeg')})
        try:
            resp = r.json()
        except ValueError:
            resp = {'ok': False, 'msg': f'respuesta inesperada ({r.status_code})'}
        if resp.get('ok'):
            registro[h] = nombre
            subidas += 1
            # Se guarda después de cada foto: si se corta la conexión, no se repite lo ya subido
            with open(registro_ruta, 'w', encoding='utf-8') as f:
                json.dump(registro, f, ensure_ascii=False, indent=1)
            print(f'  ✓ {nombre}')
        else:
            print(f'  x {nombre}: {resp.get("msg")}')

    print(f'Listo: {subidas} de {len(nuevas)} subida(s).')


if __name__ == '__main__':
    main()
