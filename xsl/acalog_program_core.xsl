<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:c="http://acalog.com/catalog/1.0" xmlns:a="http://www.w3.org/2005/Atom" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:php="http://php.net/xsl" exclude-result-prefixes="c a h php">
<xsl:output method="html" indent="no"/>
	
<xsl:param name="catoid" />

<xsl:template match="/">
    <xsl:apply-templates select="//c:cores/c:core/a:content" />
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
	
<xsl:template match="h:h1">
	<h1><xsl:apply-templates select="*|node()"/></h1>
</xsl:template>
	
<xsl:template match="h:h2">
	<h2><xsl:apply-templates select="*|node()"/></h2>
</xsl:template>

<xsl:template match="h:h3">
	<h3><xsl:apply-templates select="*|node()"/></h3>
</xsl:template>
	
<xsl:template match="h:h4">
	<h4><xsl:apply-templates select="*|node()"/></h4>
</xsl:template>
	
<xsl:template match="h:h5">
	<h5><xsl:apply-templates select="*|node()"/></h5>
</xsl:template>

<xsl:template match="h:br">
	<br/>
</xsl:template>

<xsl:template match="h:a">
	<a>
		<xsl:if test="@href">
			<xsl:attribute name="href">
				<xsl:value-of select="@href" />
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@name">
			<xsl:attribute name="name">
				<xsl:value-of select="@name" />
			</xsl:attribute>
		</xsl:if>
		<xsl:apply-templates select="*|node()"/>
	</a>
</xsl:template>
	
<xsl:template match="h:span">
	<span>
		<xsl:if test="@style">
			<xsl:attribute name="style">
				<xsl:value-of select="@style" />
			</xsl:attribute>
		</xsl:if>
		<xsl:apply-templates select="*|node()"/>
	</span>
</xsl:template>
	
<xsl:template match="h:table">
	<table>
		<xsl:if test="@border">
			<xsl:attribute name="border">
				<xsl:value-of select="@border" />
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@cellpadding">
			<xsl:attribute name="cellpadding">
				<xsl:value-of select="@cellpadding" />
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@cellspacing">
			<xsl:attribute name="cellspacing">
				<xsl:value-of select="@cellspacing" />
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@style">
			<xsl:attribute name="style">
				<xsl:value-of select="@style" />
			</xsl:attribute>
		</xsl:if>
		<xsl:apply-templates select="*|node()"/>
	</table>
</xsl:template>
	
<xsl:template match="h:tbody">
	<tbody><xsl:apply-templates select="*|node()"/></tbody>
</xsl:template>
	
<xsl:template match="h:tr">
	<tr><xsl:apply-templates select="*|node()"/></tr>
</xsl:template>
	
<xsl:template match="h:td">
	<td>
		<xsl:if test="@style">
			<xsl:attribute name="style">
				<xsl:value-of select="@style" />
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@colspan">
			<xsl:attribute name="colspan">
				<xsl:value-of select="@colspan" />
			</xsl:attribute>
		</xsl:if>
		<xsl:apply-templates select="*|node()"/>
	</td>
</xsl:template>
	
<xsl:template match="h:sup">
	<sup><xsl:apply-templates select="*|node()"/></sup>
</xsl:template>

<xsl:template match="c:permalink">
	<a href="{php:function('acalog_get_url',string(@link-id),string(@to),$catoid)}"><xsl:apply-templates select="*|node()"/></a>
</xsl:template>

</xsl:stylesheet>