/*
* C version of AgilityContest.bat in a desesperate intent of bypass aVast antivirus sucking
*
* Compile with:
* bash$ i686-w64-mingw32-gcc AgilityContest.c -mwindows -o AgilityContest.exe
*/
#include <sys/types.h>
#include <sys/stat.h>
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <windows.h>
#include <tchar.h>

FILE *logFile;

char **split (char *str) {
    char **res= calloc(32,sizeof(char *));
    int i = 1;
    char* hit = str;
    while((hit = strchr(hit, ',')) != NULL) { //Find next delimiter
        //In-place replacement of the delimiter
        *hit++ = '\0';
        //Next substring starts right after the hit
        res[i++] = hit;
    }
    return res;
}

int doLog(char *function, char *msg) {
    fputs(function,logFile);
    fputs(": ",logFile);
    fputs(msg,logFile);
    fputs("\n",logFile);
}

/**
CreateProcess(
    name
    cmdline
    process attributes
    thread attributes
    inherit handlers
    creation flags
    environment
    workingdir
    startup info
    process info
*/
int launchAndWait (char *cmd, char *args) {
    doLog("launchAndWait cmd",cmd);
    doLog("launchAndWait args",args);
    PROCESS_INFORMATION ProcessInfo; //This is what we get as an [out] parameter

    STARTUPINFO StartupInfo; //This is an [in] parameter

    ZeroMemory(&StartupInfo, sizeof(StartupInfo));
    StartupInfo.cb = sizeof StartupInfo ; //Only compulsory field

    if(CreateProcess(cmd, TEXT(args), NULL,NULL,FALSE,CREATE_NO_WINDOW,NULL, NULL,&StartupInfo,&ProcessInfo))  {
        WaitForSingleObject(ProcessInfo.hProcess,INFINITE);
        CloseHandle(ProcessInfo.hThread);
        CloseHandle(ProcessInfo.hProcess);
         // MessageBox (NULL, args,"success", MB_OK | MB_ICONINFORMATION);
    } else {
        MessageBox (NULL, args,"failed", MB_OK | MB_ICONINFORMATION);
    }
    return 0;
}

int launchAndForget ( char *cmd, char *args,PROCESS_INFORMATION *ProcessInfo,STARTUPINFO *StartupInfo) {
    doLog("launchAndForget cmd",cmd);
    doLog("launchAndForget args",args);
    if(! CreateProcess(cmd, TEXT(args), NULL,NULL,FALSE,CREATE_NO_WINDOW,NULL, NULL,StartupInfo,ProcessInfo))  {
        MessageBox (NULL, args,"failed", MB_OK | MB_ICONINFORMATION);
    } else {
        CloseHandle(ProcessInfo->hThread);
        CloseHandle(ProcessInfo->hProcess);
    }
    return 0;
}

int first_install() {
    struct stat buffer;
    int         status;
    status = stat("..\\logs\\first_install",&buffer);
    doLog("first_install",(status==0)?"true":"false");
    return (status==0)?1:0; // true on success
}

int WINAPI WinMain (HINSTANCE hInstance, HINSTANCE hPrevInst, LPTSTR lpCmdLine, int nShowCmd) {

    STARTUPINFO mysqld_si;
    PROCESS_INFORMATION mysqld_pi;
    ZeroMemory( &mysqld_si, sizeof(mysqld_si) );
    mysqld_si.cb = sizeof(mysqld_si);
    ZeroMemory( &mysqld_pi, sizeof(mysqld_pi) );

    STARTUPINFO apache_si;
    PROCESS_INFORMATION apache_pi;
    ZeroMemory( &apache_si, sizeof(apache_si) );
    apache_si.cb = sizeof(apache_si);
    ZeroMemory( &apache_pi, sizeof(apache_pi) );

    CONST HANDLE handlers[] = { mysqld_pi.hProcess,apache_pi.hProcess };

    logFile=fopen(".\\logs\\startup.log","w");

    // @echo off
    char *msg="Hello World!\n";
    doLog("init",msg);
    // call settings.bat
    // settings.bat sets default language. So just parse and setenv
    FILE *f=fopen(".\\settings.bat","r");
    if (f) {
        char *str=calloc(32,sizeof(char));
        fgets(str,31,f);
        fclose(f);
        msg=1+strchr(str,' ');
        putenv(msg);
        doLog("setenv",msg);
    }

    // cd /d %~dp0\xampp
    // rem echo AgilityContest Launch Script
    // set working directory to ${cwd}\xampp
    char *wd=calloc(256,sizeof(char)); // enought to store current and new directory
    getcwd(wd,255);
    strncat(wd,"\\xampp",250);
    chdir(wd);
    doLog("chdir",wd);

    // presenta mensaje de arranque...
    char *cmd="start \"\" /B mshta \"javascript:var sh=new ActiveXObject( 'WScript.Shell' ); sh.Popup( 'AgilityContest is starting. Please wait', 20, 'Working...', 64 );close()\"";
    system(cmd);

    // rem notice that this may require admin privileges
    // rem for windows 8 and 10 disable w3svc service
    // rem also configure firewall to allow http https and mysql

    // net stop W3SVC
    cmd="C:\\Windows\\system32\\net.exe";
    char *args="C:\\Windows\\system32\\net.exe stop W3SVC";
    launchAndWait(cmd,args);
    // netsh advfirewall firewall add rule name=\"MySQL Server\" action=allow protocol=TCP dir=in localport=3306
    cmd="C:\\Windows\\system32\\netsh.exe";
    args="C:\\Windows\\system32\\netsh.exe advfirewall firewall add rule name=\"MySQL Server\" action=allow protocol=TCP dir=in localport=3306";
    launchAndWait(cmd,args);
    // netsh advfirewall firewall add rule name=\"Apache HTTP Server\" action=allow protocol=TCP dir=in localport=80
    cmd="C:\\Windows\\system32\\netsh.exe";
    args="C:\\Windows\\system32\\netsh.exe advfirewall firewall add rule name=\"Apache HTTP Server\" action=allow protocol=TCP dir=in localport=80";
    launchAndWait(cmd,args);

    // netsh advfirewall firewall add rule name=\"Apache HTTPs Server\" action=allow protocol=TCP dir=in localport=443
    cmd="C:\\Windows\\system32\\netsh.exe";
    args="C:\\Windows\\system32\\netsh.exe advfirewall firewall add rule name=\"Apache HTTPs Server\" action=allow protocol=TCP dir=in localport=443";
    launchAndWait(cmd,args);

    /* on first install set properly php environment (paths, configs and so ) */
    if ( first_install() ) {
        // rem if required prepare portable xampp to properly setup directories
        // if not exist ..\logs\first_install GOTO mysql_start
        // rem echo Configuring first boot of XAMPP
        // set PHP_BIN=php\php.exe
        // set CONFIG_PHP=install\install.php
        // %PHP_BIN% -n -d output_buffering=0 -q %CONFIG_PHP% usb >nul
        char *php=calloc(32+strlen(wd),sizeof(char));
        char *phpargs=calloc(256+strlen(wd),sizeof(char));
        sprintf(php,"%s\\php\\php.exe",wd);
        sprintf(phpargs,"%s\\php\\php.exe -n -d output_buffering=0 -q install\\install.php usb >nul",wd);
        launchAndWait(php,phpargs);
    }

    // rem start mysql database server
    // :mysql_start
    // rem echo MySQL Database is trying to start
    // rem echo Please wait  ....
    // start "" /B mysql\bin\mysqld --defaults-file=mysql\bin\my.ini --standalone --console >nul
    // rem timeout  5
    // ping -n 5 127.0.0.1 >nul
    char *mysqld=calloc(32+strlen(wd),sizeof(char));
    char *mysqldargs=calloc(256+strlen(wd),sizeof(char));
    sprintf(mysqld,"%s\\mysql\\bin\\mysqld.exe",wd);
    sprintf(mysqldargs,"--defaults-file=mysql\\bin\\my.ini --standalone --console",wd);
    launchAndForget(mysqld,mysqldargs,&mysqld_pi,&mysqld_si);
    // system("start \"\" /B mysql\\bin\\mysqld --defaults-file=mysql\\bin\\my.ini --standalone --console >nul");
    sleep(7);

    // rem start apache web server
    // :apache_start
    // rem echo Starting Apache Web Server....
    // start "" /B apache\bin\httpd.exe >nul
    // rem timeout  5
    // ping -n 5 127.0.0.1 >nul
    char *apache=calloc(32+strlen(wd),sizeof(char));
    char *apacheargs=calloc(256+strlen(wd),sizeof(char));
    sprintf(apache,"%s\\apache\\bin\\httpd.exe",wd);
    sprintf(apacheargs,"",wd);
    launchAndForget(apache,apacheargs,&apache_pi,&apache_si);
    // system("start \"\" /B apache\\bin\\httpd.exe");
    sleep(7);

    /* create database and basic data on first install */
    // if not exist ..\logs\first_install GOTO browser_start
    if ( first_install() ) {
        // rem echo Creating AgilityContest Databases. Please wait
        //timeout /t 5
        char *buff=calloc(1024,sizeof(char));
        FILE *f=fopen("..\\logs\\install.sql","w");
        FILE *u=fopen("..\\extras\\users.sql","r");
        sleep(5); // extra timeout for let mysqld extra time to start

        // echo DROP DATABASE IF EXISTS agility; > ..\logs\install.sql
        // echo CREATE DATABASE agility; >> ..\logs\install.sql
        // echo USE agility; >> ..\logs\install.sql
        // rem type ..\extras\agility.sql >> ..\logs\install.sql
        // type ..\extras\users.sql >> ..\logs\install.sql
        fputs("DROP DATABASE IF EXISTS agility;\n",f);
        fputs("CREATE DATABASE agility;\n",f);
        fputs("USE AGILITY;\n",f);
        while(fgets(buff,1024,u)!=NULL) fputs(buff,f);
        fclose(u);
        fclose(f);

        // rem on first run create database and database users
        // mysql\bin\mysql -u root < ..\logs\install.sql
        char *mysql=calloc(32+strlen(wd),sizeof(char));
        char *mysqlargs=calloc(256+strlen(wd),sizeof(char));
        sprintf(mysql,"%s\\mysql\\bin\\mysql.exe",wd);
        sprintf(mysqlargs,"%s\\mysql\\bin\\mysql.exe -u root < ..\\logs\\install.sql",wd);
        launchAndWait(mysql,mysqlargs);
    }

    /*
    del ..\logs\install.sql
    del ..\logs\first_install
    rem echo Opening AgilityContest console for first time...
    start /MAX "AgilityContest" https://localhost/agility/console/index.php?installdb=1
    goto wait_for_end
    rem normal start when database is installed
    :browser_start
    rem echo Opening AgilityContest console...
    start /MAX "AgilityContest" https://localhost/agility/console
    */
    char *browser="start /MAX \"AgilityContest\" https://localhost/agility/console";
    if (first_install() ) {
        browser="start /MAX \"AgilityContest\" https://localhost/agility/console/index.php?installdb=1";
    }
    // del ..\logs\install.sql
    // del ..\logs\first_install
    // unlink("..\\logs\\install.sql");
    // unlink("..\\logs\\first_install.sql");
    doLog("system",browser);
    system(browser);

    // :wait_for_end
    // exit
    doLog("wait","");
    fclose (logFile);
    WaitForMultipleObjects ( 2,handlers,1,INFINITE);
    return 0;
}

