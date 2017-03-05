sub includeFile (fSpec)
    dim fileSys, file, fileData
    set fileSys = createObject ("Scripting.FileSystemObject")
    set file = fileSys.openTextFile (fSpec)
    fileData = file.readAll ()
    file.close
    executeGlobal fileData
    set file = nothing
    set fileSys = nothing
end sub
includeFile "common.locale.vbs"

strSourcesDir     = WshShell.CurrentDirectory
strDestinationDir = Replace(Replace(strCurDir, "locale", ""), "sources", "public_html")

'ShowSubfolders objFSO.GetFolder(WshShell.CurrentDirectory), "Compile", "", ""
GenerateIndividualLocaleFromPortableObjectTemplate()
CompileLocale()

MsgBox "I finished!"
