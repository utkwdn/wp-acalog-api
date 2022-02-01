<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:c="http://acalog.com/catalog/1.0" xmlns:a="http://www.w3.org/2005/Atom" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:php="http://php.net/xsl" exclude-result-prefixes="c a h php">
<xsl:output method="html" indent="no"/>

<xsl:param name="progid" />

<xsl:template match="/">
	<xsl:value-of select="//c:program[@id=$progid]/c:cores/c:core/c:children/c:core/a:title" /> 
</xsl:template>

</xsl:stylesheet>