<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:c="http://acalog.com/catalog/1.0" xmlns:a="http://www.w3.org/2005/Atom" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:php="http://php.net/xsl">
<xsl:output method="html" indent="no"/>

<xsl:param name="cattype" />

<xsl:template match="/">  
    <xsl:for-each select="//c:catalogs/c:catalog">
    	<xsl:if test="c:type/c:type/a:title = $cattype and c:state/c:published = 'Yes' and c:state/c:archived = 'No'">
        	<xsl:value-of select="@id" />
        </xsl:if>
    </xsl:for-each>    
</xsl:template>

</xsl:stylesheet>