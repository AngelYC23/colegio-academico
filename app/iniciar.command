#!/bin/zsh
# Arranque local para el taller; no instala ni modifica programas del Mac.
cd "$(dirname "$0")" || exit 1
if command -v php >/dev/null 2>&1; then
    task_php="$(command -v php)"
elif [[ -x /Applications/XAMPP/xamppfiles/bin/php ]]; then
    task_php=/Applications/XAMPP/xamppfiles/bin/php
else
    echo 'Se requiere PHP 8.2 o superior (PHP 8.3 para las herramientas de desarrollo).'
    exit 1
fi
if [[ ! -f vendor/autoload.php || ! -f .env ]]; then
    echo 'Falta la preparación inicial. Consulta README.md antes de iniciar.'
    exit 1
fi
echo 'La Generación del Mañana · http://127.0.0.1:8088/ingresar'
echo 'Deja esta ventana abierta. Para detener: Control + C.'
"$task_php" artisan serve --host=127.0.0.1 --port=8088 --tries=1
