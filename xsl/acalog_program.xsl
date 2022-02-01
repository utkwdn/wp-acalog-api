<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:c="http://acalog.com/catalog/1.0" xmlns:a="http://www.w3.org/2005/Atom" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:php="http://php.net/xsl" exclude-result-prefixes="c a h php">
<xsl:output method="html" indent="no"/>

<xsl:template match="/">
	<div class="acaprog">
	<h1><xsl:value-of select="//c:program/a:title" /></h1>
    <xsl:apply-templates select="//c:program/a:content" />
    <xsl:apply-templates select="//c:cores/c:core" />
    </div>
</xsl:template>

<xsl:template match="a:content">
	<xsl:if test="string-length(string(h:p)) > 1">
		<xsl:apply-templates select="*|node()"/>
    </xsl:if>
</xsl:template>

<xsl:template match="h:p">
	<p><xsl:apply-templates select="*|node()"/></p>
</xsl:template>

<xsl:template match="h:strong">
	<strong><xsl:apply-templates select="*|node()"/></strong>
</xsl:template>

<xsl:template match="h:b">
	<strong><xsl:apply-templates select="*|node()"/></strong>
</xsl:template>

<xsl:template match="h:ul">
	<ul><xsl:apply-templates select="*|node()"/></ul>
</xsl:template>

<xsl:template match="h:ol">
	<ol><xsl:apply-templates select="*|node()"/></ol>
</xsl:template>

<xsl:template match="h:li">
	<li><xsl:apply-templates select="*|node()"/></li>
</xsl:template>

<xsl:template match="h:em">
	<em><xsl:apply-templates select="*|node()"/></em>
</xsl:template>

<xsl:template match="h:i">
	<em><xsl:apply-templates select="*|node()"/></em>
</xsl:template>

<xsl:template match="h:h5">
	<h5><xsl:apply-templates select="*|node()"/></h5>
</xsl:template>

<xsl:template match="h:br">
	<br/>
</xsl:template>

<xsl:template match="h:a">
	<a href="{@href}"><xsl:apply-templates select="*|node()"/></a>
</xsl:template>

<xsl:template match="c:core">
	<h3><xsl:value-of select="a:title"/></h3>
    <xsl:apply-templates select="a:content" />
	<xsl:apply-templates select="c:courses">
    	<xsl:with-param name="coreid" select="@id"/>
    </xsl:apply-templates>
	<xsl:apply-templates select="c:children" />
</xsl:template>

<xsl:template match="c:course">
	<xsl:param name="courseid" select="@id"/>
    <xsl:param name="coreid" />
    <xsl:variable name="credits" select="a:content/c:field/c:data" />
	<ul><li>
    <div id="{$courseid}" class="acalog-course-name"><a style="cursor:pointer; cursor:hand"><xsl:value-of select="a:title"/></a>
    <strong>&#160;
    <xsl:choose>
    	<xsl:when test="not(contains($credits,'credit hours'))">
          	<xsl:value-of select="$credits" /> credit hours
        </xsl:when>
        <xsl:otherwise>
           	<xsl:value-of select="$credits" />
        </xsl:otherwise>
    </xsl:choose>
    &#160;</strong>
    <xsl:value-of select="//c:adhoc[@course=$courseid and @core=$coreid and @position='right']/a:content" /></div>
    <div class="acalog-course-info {$courseid}"><xsl:value-of select="php:function('get_course',string($courseid))" disable-output-escaping="yes" /></div>
    </li></ul>
</xsl:template>

<xsl:template match="c:adhoc">
	<xsl:if test="@position='after'">
    	<xsl:apply-templates select="a:content"/>
    </xsl:if>
    <xsl:if test="@position='before'">
    	<xsl:apply-templates select="a:content"/>
    </xsl:if>
    <xsl:if test="a:title='blank'">
    	<p>&#160;</p>
    </xsl:if>
    <xsl:if test="a:title='BLANK'">
    	<p>&#160;</p>
    </xsl:if>
    <xsl:if test="a:title='blank line'">
    	<p>&#160;</p>
    </xsl:if>
</xsl:template>

<xsl:template match="c:permalink">
	<a href="{php:function('get_url',string(@link-id),string(@to))}"><xsl:apply-templates select="*|node()"/></a>
</xsl:template>

</xsl:stylesheet>