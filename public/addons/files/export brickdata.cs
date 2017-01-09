//exports all brick datablocks as a json object
//NOTE: degree symbol is output as 0xb0
//this may require a manual fix
//converting to utf-8 and back to ansi seems to fix?

function export_brickdata(%filepath)
{
   %file = new fileobject();
   %file.openforwrite(%filepath);

   %file.writeline("{");
   %count = 0;

   for(%i=0; %i<datablockgroup.getcount(); %i++)
   {
      %o = datablockgroup.getobject(%i);

      if(isobject(%o) && %o.getclassname() $= "fxdtsbrickdata")
      {
         %count++;
         %file.writeline("\t\"" @ %o.uiname @ "\" : {");
         %file.writeline("\t\t\"x\" : " @ %o.bricksizex @ ",");
         %file.writeline("\t\t\"y\" : " @ %o.bricksizey @ ",");
         %file.writeline("\t\t\"z\" : " @ %o.bricksizez @ ",");
         %file.writeline("\t\t\"n\" : " @ %o.cancovernorth @ ",");
         %file.writeline("\t\t\"s\" : " @ %o.cancoversouth @ ",");
         %file.writeline("\t\t\"e\" : " @ %o.cancovereast @ ",");
         %file.writeline("\t\t\"w\" : " @ %o.cancoverwest @ ",");
         %file.writeline("\t\t\"t\" : " @ %o.cancovertop @ ",");
         %file.writeline("\t\t\"b\" : " @ %o.cancoverbottom @ ",");
         %file.writeline("\t\t\"water\" : " @ %o.iswaterbrick);
         %file.writeline("\t},");
      }
   }
   %file.writeline("\t\"count\" : " @ %count);
   %file.writeline("}");
   %file.close();
   %file.delete();
}

