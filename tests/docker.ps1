param([string[]]$Versions = @('5.6','7.0','7.4','8.1','8.4','8.5'))
$ErrorActionPreference = 'Stop'
Push-Location (Join-Path $PSScriptRoot '..')
try {
    foreach ($version in $Versions) {
        $dockerfile = if ($version -in @('5.6','7.0')) { 'tests/Dockerfile.legacy' } else { 'tests/Dockerfile' }
        docker build -q --build-arg "PHP_VERSION=$version" -t "nyaaxport-test:$version" -f $dockerfile .
        if ($LASTEXITCODE -ne 0) { throw "Cannot build PHP $version test image" }
        docker run --rm --read-only --tmpfs /tmp --network none "nyaaxport-test:$version" sh -c 'php -l nyaaxport.php && php -l nyaaxport_56.php && php -l nyaaxport_cloud.php && php tests/regression.php'
        if ($LASTEXITCODE -ne 0) { throw "PHP $version regression failed" }
    }
} finally { Pop-Location }
