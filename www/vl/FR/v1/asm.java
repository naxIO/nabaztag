/* This app is for compiling nabaztag V1 sources into a binary that can be sent to the rabbit.  The file format is:
 -write 7F
 -write 05
 -write 3 bytes indicating the length of the entire byte code + 5 (should include "mind" & starts after these bytes)
 -write 5 bytes containing the word "amber"
 -write 4 bytes containing the index = 00000001
 -write 1 byte containing the priority =01
 -write 4 bytes indicating the length of the message
 -write the message compiled with vasm.java 
 -write 4 bytes indicating a music file = 01 if music 
 -write 4 bytes indicating the size of the music file
 -write the music file
 -write 1 byte checksum
 -write 4 bytes containing the word "mind"
 -write 1 byte containing FF
 -write 1 byte containing 0A
 
 It uses vasm.java written by Sylvain Huet, the developer of the rabbit.  You need to compile vasm.java separately.
*/

import java.io.*;

public final class asm
{
public static void main(String args[]) 
{
	log("hello");

    if(args.length < 2)
	{
		log("Usage: asm inputfile outputfile musicfile");
        log("Example: asm hello.vasm hello.bin");
        log("         asm hello.vasm hello.bin 1.mid");
		return;
	}
    
    
	log("Compiling file " + args[0] + " to " + args[1]);
	
    Vasm vasm = new Vasm();
	String sInFile = args[0];
	String sOutFile = args[1];
	
	String sMusicFile="";
	boolean bMusic = false;
    
	if(args.length > 2)
    {
		sMusicFile = args[2];  //noboot = don't include boot.bin
        bMusic = true;
    }

	File f = new File(sInFile);
    
    if(!f.exists())
    {
        log(sInFile + " does not exist");
        return;
    }
    
    if(bMusic)
    {
        f = new File(sMusicFile);
        
        if(!f.exists())
        {
            log(sMusicFile + " does not exist");
            return;
        }
    }
                    
	File f1 = new File(sOutFile);
	f1.delete();
	
	String sCode = vasm.getfile(sInFile,".");
	String sComp="";
	
	try
	{
		sComp = vasm.asm(sCode,17,".");  //code, offset, path
	}
	catch(Exception e)
	{
		log("Error compiling. " + e);
	}

	int iLen = sComp.length();
	
	log("sComp length is " + iLen);
	
	if(iLen < 1)
	{
		log("Assembled length < 0.  Aborting");
		return;
	}
	
	byte[] aCode=null; //null;
	
	try
	{
		if(bMusic)
        {
			aCode=sComp.substring(0,iLen).getBytes("ISO-8859-1");
            aCode[aCode.length-1] = 1; 
        }
        else
			//aCode=sComp.substring(0,iLen-4).getBytes("ISO-8859-1");
            aCode=sComp.substring(0,iLen).getBytes("ISO-8859-1");

	}
	catch(Throwable t)
	{
		log("Error converting to ISO.  Aborting.");
		return;
	}
	
    // log("");
	log("aCode length is " + aCode.length);
	
	//writeFile("mybin.bin", aCode); //Comp.getBytes());
	
	//for(int i=0; i < aCode.length; i++)
		//System.out.print(Integer.toHexString(aCode[i]&0xff) + " ");
	
    //log("");
    
	byte[] aPgm = aCode; 
	byte[] aMusic = {};
	byte[] aMusicLen = {0,0,0,0};
    int iMusicLen = 4;  //always have 4 zeros
    
    if(bMusic)
    {
        aMusic = readFile(sMusicFile);
        iMusicLen  = aMusic.length;
        
        log("music file length is " + iMusicLen);
        
        aMusicLen[0] = 0; //(byte) (iMusicLen >>> 32);
        aMusicLen[1] = (byte) (iMusicLen >>> 16);
        aMusicLen[2] = (byte) (iMusicLen >>> 8);
        aMusicLen[3] = (byte) iMusicLen;
        
        iMusicLen = 4;  //for output
    }
    
    log("program length is " + aPgm.length);
	
	iLen = aPgm.length + iMusicLen + aMusic.length + 15;  //15 for header
	
	log("bin length is " + iLen);
	    
    //log("Music file length is " + (aMusicLen[0] + aMusicLen[1] + aMusicLen[2] + aMusicLen[3]));
    
	byte[] a7f = {0x7f};
	
	byte[] aIntro = {5
	                , (byte) (iLen >>> 16)
	                , (byte) (iLen >>> 8)
	                , (byte) iLen
	                ,'a','m','b','e','r'
	                ,0
	                ,0
	                ,0
	                ,1 //index
	                ,1}; //priority
	                
	writeFile(sOutFile, a7f);
	writeFile(sOutFile, aIntro);
	writeFile(sOutFile, aPgm);
    writeFile(sOutFile, aMusicLen);
	writeFile(sOutFile, aMusic);
	
	byte[] aRes = new byte[aIntro.length + aPgm.length + aMusicLen.length + aMusic.length];
	
	int i,j;
	
	for(i=0; i < aIntro.length; i++)
		aRes[i] = aIntro[i];
	
	for(j=0; j < aPgm.length; j++)
	{
		aRes[i] = aPgm[j];
		i++;
	}
	
    for(j=0; j < aMusicLen.length; j++)
	{
		aRes[i] = aMusicLen[j];
		i++;
	}
	
	for(j=0; j < aMusic.length; j++)
	{
		aRes[i] = aMusic[j];
		i++;
	}
	
    int iMind = checksum("mind");
	int iRes1  = checksum(aRes,0);
	int iFinal = 255-iRes1-iMind;
	iFinal&=255;
	
	log("Checksum is " + Integer.toHexString(iFinal));
	
	byte[] aEnd = {(byte)iFinal
	              ,'m'
	              ,'i'
	              ,'n'
	              ,'d'
	              ,(byte)0xff
	              }; //,(byte)0x0a};
	
	writeFile(sOutFile, aEnd);
	
	log("Finished");
	
}


private static int checksum(byte[] buf, int iStart)
{
		int l=buf.length;
		int i=0;
		byte chk=0;
		for(i=iStart;i<l;i++) chk+=buf[i];
		return (int)chk;
}

private static void writeFile(String sFile, int[] bytes)
{
	byte[] emptybuf = null;
	
	try
	{
		RandomAccessFile file = new RandomAccessFile(new File(sFile), "rw");
		
		file.seek(file.length()); //append
		
		int i=0;
		
		for(i=0; i < bytes.length; i++)
		{
			file.writeByte(bytes[i]);
		}
		
		log("Wrote " + i + " bytes to " + sFile);
		file.close();
		return;
	}
	catch (IOException es)
	{
		log("File not found.");
	}
	
	return;
	
}

private static void writeFile(String sFile, byte[] bytes)
{
	byte[] emptybuf = null;
	
	try
	{
		RandomAccessFile file = new RandomAccessFile(new File(sFile), "rw");
		
		file.seek(file.length()); //append
		
		int i=0;
		
		for(i=0; i < bytes.length; i++)
		{
			file.writeByte(bytes[i]);
		}
		
		log("Wrote " + i + " bytes to " + sFile);
		file.close();
		return;
	}
	catch (IOException es)
	{
		log("File not found.");
	}
	
	return;
	
}

private static byte[] readFile(String sFile)
{
	byte[] emptybuf = null;
	
	try
	{
		RandomAccessFile file = new RandomAccessFile(new File(sFile), "r");
		long iLen = file.length();
		log(sFile + " length is " + iLen);
		byte[] buf = new byte[(int)iLen];
		//int[] buf = new int[(int)iLen];
		file.readFully(buf);
		file.close();
		return buf;
	}
	catch (IOException es)
	{
		log("File not found.");
	}
	
	return emptybuf;
	
}

private static int checksum(String src)
{
	try{
		byte[] buf=src.getBytes("ISO-8859-1");
		int l=src.length();
		int i=0;
		byte chk=0;
		for(i=0;i<l;i++) chk+=buf[i];
		return (int)chk;
	} catch (Throwable t) {
				log("*** check sum error");
    		return -1;
	}
}


private static void writeFile(String sData, String sFile)
{
try
{
  // Create file 
  FileWriter fstream = new FileWriter(sFile);
  BufferedWriter out = new BufferedWriter(fstream);
  out.write(sData);
  out.close();
}catch (Exception e)
  {//Catch exception if any
  	System.err.println("Error: " + e.getMessage());
  }
}

private static void log(String sMsg)
{
	System.out.println(sMsg);
}



}