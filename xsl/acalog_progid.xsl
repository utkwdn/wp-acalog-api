<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
<xsl:output method="html" indent="no"/>

<xsl:param name="progname" />

<xsl:template match="/">  
    <xsl:for-each select="//results/result">
    	<xsl:if test="name = $progname">
        	<xsl:value-of select="id" />
        </xsl:if>
    </xsl:for-each>    
</xsl:template>

</xsl:stylesheet>