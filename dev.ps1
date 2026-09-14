# Helper script for Docker-based development on Windows (PowerShell)
# Usage: .\dev.ps1 [command]

param (
    [Parameter(Position=0)]
    [string]$Command = "help",

    [Parameter(Position=1, ValueFromRemainingArguments=$true)]
    [string[]]$ArgsList
)

function Is-AppRunning {
    $running = docker compose ps --status running --format "{{.Service}}" 2>$null
    return ($running -contains "app")
}

function Run-InApp {
    param ([string[]]$CmdArgs)
    if (Is-AppRunning) {
        docker compose exec -u www-data app @CmdArgs
    } else {
        docker compose run --rm -u www-data app @CmdArgs
    }
}

function Is-NodeRunning {
    $running = docker compose ps --status running --format "{{.Service}}" 2>$null
    return ($running -contains "node")
}

function Run-InNode {
    param ([string[]]$CmdArgs)
    if (Is-NodeRunning) {
        docker compose exec node @CmdArgs
    } else {
        docker compose run --rm node @CmdArgs
    }
}

switch ($Command) {
    { $_ -in "start", "up" } {
        docker compose up -d @ArgsList
        Write-Host "Containers started!" -ForegroundColor Green
        Write-Host "Web Application : http://localhost:8000" -ForegroundColor Cyan
        Write-Host "Vite HMR Server : http://localhost:5173" -ForegroundColor Cyan
    }

    { $_ -in "stop", "down" } {
        docker compose down @ArgsList
    }

    "restart" {
        docker compose restart @ArgsList
    }

    { $_ -in "ps", "status" } {
        docker compose ps @ArgsList
    }

    "logs" {
        docker compose logs -f @ArgsList
    }

    { $_ -in "artisan", "art" } {
        Run-InApp @("php", "artisan") + $ArgsList
    }

    { $_ -in "composer", "comp" } {
        Run-InApp @("composer") + $ArgsList
    }

    "npm" {
        Run-InNode @("npm") + $ArgsList
    }

    "build" {
        Run-InNode @("npm", "run", "build") + $ArgsList
    }

    "fresh" {
        Run-InApp @("php", "artisan", "migrate:fresh", "--seed") + $ArgsList
    }

    "clear" {
        Run-InApp @("php", "artisan", "optimize:clear") + $ArgsList
    }

    "test" {
        Run-InApp @("php", "artisan", "test") + $ArgsList
    }

    "pint" {
        Run-InApp @("vendor/bin/pint") + $ArgsList
    }

    { $_ -in "bash", "shell" } {
        docker compose exec -it -u www-data app bash
    }

    "root-bash" {
        docker compose exec -it app bash
    }

    "mysql" {
        Run-InApp @("php", "artisan", "db") + $ArgsList
    }

    Default {
        Write-Host "==========================================================" -ForegroundColor Yellow
        Write-Host " Infora Project - Docker Development Helper (PowerShell)" -ForegroundColor Yellow
        Write-Host "==========================================================" -ForegroundColor Yellow
        Write-Host "Usage: .\dev.ps1 <command> [args]"
        Write-Host ""
        Write-Host "Service Management:"
        Write-Host "  start, up       Start all docker containers in background"
        Write-Host "  stop, down      Stop and remove containers"
        Write-Host "  restart         Restart containers"
        Write-Host "  ps, status      Show container status"
        Write-Host "  logs [service]  Tail container logs (e.g. .\dev.ps1 logs app, .\dev.ps1 logs web)"
        Write-Host ""
        Write-Host "Development & Tools:"
        Write-Host "  artisan <args>  Run artisan command (e.g. .\dev.ps1 artisan migrate)"
        Write-Host "  composer <args> Run composer command (e.g. .\dev.ps1 composer install)"
        Write-Host "  npm <args>      Run npm command (e.g. .\dev.ps1 npm install lodash)"
        Write-Host "  build           Build frontend assets for production (npm run build)"
        Write-Host "  fresh           Reset database & run seeders (migrate:fresh --seed)"
        Write-Host "  clear           Clear all Laravel caches (optimize:clear)"
        Write-Host "  test <args>     Run Pest/PHPUnit tests (e.g. .\dev.ps1 test)"
        Write-Host "  pint <args>     Run Laravel Pint formatter"
        Write-Host ""
        Write-Host "Terminal & DB:"
        Write-Host "  bash            Open bash shell in PHP container (as www-data)"
        Write-Host "  root-bash       Open bash shell in PHP container (as root)"
        Write-Host "  mysql           Open MySQL CLI into configured database (via artisan db)"
        Write-Host "==========================================================" -ForegroundColor Yellow
    }
}
