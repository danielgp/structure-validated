Set objFSO = WScript.CreateObject("Scripting.FileSystemObject")
Set WshShell   = WScript.CreateObject("WScript.Shell")

PortableObjectTemplate_FileName = "structure-validated.pot"
SupportedLocale = Array("en_US", "ro_RO")
strSourcesDir     = WshShell.CurrentDirectory

Sub CompileLocale()
    For Each currentLocale In SupportedLocale
        SourcePath = strSourcesDir
        SourceFileName = Replace(PortableObjectTemplate_FileName, ".pot", "---" & currentLocale & ".po")
        DestinationFileName = Replace(PortableObjectTemplate_FileName, ".pot", ".mo")
        DestinationPath = strSourcesDir & "\" & currentLocale & "\LC_MESSAGES"
        If Not objFSO.FolderExists(strSourcesDir & "\" & currentLocale) Then
            objFSO.CreateFolder(strSourcesDir & "\" & currentLocale)
        End If
        If Not objFSO.FolderExists(DestinationPath) Then
            objFSO.CreateFolder(DestinationPath)
        End If
        WshShell.Run "C:\www\AppForDeveloper\GetText\msgfmt.exe " & SourcePath & "\" & SourceFileName & _ 
            " --output-file=" & DestinationPath & "\" & DestinationFileName & _
            " --statistics --check --verbose", 0, True
    Next
End Sub
Sub GenerateIndividualLocaleFromPortableObjectTemplate()
    For Each currentLocale In SupportedLocale
        TargetLocaleFileName = Replace(PortableObjectTemplate_FileName, ".pot", "---" & currentLocale & ".po")
        If (Not objFSO.FileExists(TargetLocaleFileName)) Then
            WshShell.Run "C:\www\AppForDeveloper\GetText\msginit.exe --locale=" & currentLocale & " --output-file=" & strSourcesDir & "\" & TargetLocaleFileName & " --input=" & strSourcesDir & "\" & PortableObjectTemplate_FileName
        End If
    Next
End Sub