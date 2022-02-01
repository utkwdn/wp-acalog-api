<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:c="http://acalog.com/catalog/1.0" xmlns:a="http://www.w3.org/2005/Atom" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:php="http://php.net/xsl" exclude-result-prefixes="c a h php">
<xsl:output method="html" indent="no"/>

<xsl:param name="courseid" />

<xsl:template match="/">  
    <xsl:for-each select="//c:course[@id=$courseid]/a:content/c:field">    
    	<xsl:if test="c:field/a:title = 'Description'">
        	<xsl:variable name="fieldid" select="@type" />
            <xsl:apply-templates select="//c:course[@id=$courseid]/a:content/c:field[@type=$fieldid]/c:data" />
        </xsl:if>
    </xsl:for-each>    
</xsl:template>

<xsl:template match="h:p">
	<p><xsl:apply-templates select="*|node()"/></p>
</xsl:template>

<xsl:template match="h:strong">
	<strong><xsl:apply-templates select="*|node()"/></strong>
</xsl:template>

<xsl:template match="h:ul">
	<ul><xsl:apply-templates select="*|node()"/></ul>
</xsl:template>

<xsl:template match="h:li">
	<li><xsl:apply-templates select="*|node()"/></li>
</xsl:template>

<xsl:template match="h:em">
	<em><xsl:apply-templates select="*|node()"/></em>
</xsl:template>

</xsl:stylesheet>