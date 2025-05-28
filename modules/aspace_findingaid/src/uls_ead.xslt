<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:ead="urn:isbn:1-931666-22-9"
  xmlns:xlink="http://www.w3.org/1999/xlink"
  xmlns:php="http://php.net/xsl"
  exclude-result-prefixes="xsl ead xlink php"
>
  <xsl:param name="containerlist_string">Container List</xsl:param>
  <xsl:param name="container_string">Containers</xsl:param>
  <xsl:param name="unitid_string">Unit ID</xsl:param>
  <xsl:param name="physdesc_string">Extent</xsl:param>
  <xsl:param name="physloc_string">Physical Location</xsl:param>
  <xsl:param name="langmaterial_string">Language</xsl:param>
  <xsl:param name="controlaccess_string">Subjects</xsl:param>
  <xsl:param name="corpname_string">Corporate Names</xsl:param>
  <xsl:param name="persname_string">Personal Names</xsl:param>
  <xsl:param name="famname_string">Family Names</xsl:param>
  <xsl:param name="geogname_string">Geographic Names</xsl:param>
  <xsl:param name="occupation_string">Occupations</xsl:param>
  <xsl:param name="subject_string">Other Subjects</xsl:param>
  <xsl:param name="genreform_string">Genres</xsl:param>
  <xsl:param name="recordgrp_string">Record Group</xsl:param>
  <xsl:param name="subgrp_string">Subgroup</xsl:param>
  <xsl:param name="series_string">Series</xsl:param>
  <xsl:param name="subseries_string">Subseries</xsl:param>
  <xsl:param name="otherlevel_string">Section</xsl:param>
  <xsl:param name="subfonds_string">Subfonds</xsl:param>
  <xsl:param name="file_string">File</xsl:param>
  <xsl:param name="item_string">Item</xsl:param>

  <!--ArchivesSpace module resource uri prefix -->
  <xsl:param name="viewonlineuri"  select="'https://digital.library.pitt.edu/islandora/object/pitt:'"/>
  <!--Ensure to add a Question Mark in base_aeon_url to signify the beginning of query parameters -->
  <xsl:param name="base_aeon_url"  select="'https://pitt.aeon.atlas-sys.com/logon?Action=10&amp;Form=20&amp;value=GenericRequestManuscript'"/>

  <xsl:template match="/">
    <div class="ead">
      <xsl:apply-templates select="//ead:archdesc"/>
    </div>
  </xsl:template>

  <xsl:template match="ead:scopecontent">
    <xsl:apply-templates select="ead:head">
      <xsl:with-param name="heading_level">
        <!-- if we are in the root archdesc, default to h2, otherwise h3 -->
        <xsl:choose>
          <xsl:when test="../../ead:archdesc">
            <xsl:text>h2</xsl:text>
          </xsl:when>
          <xsl:otherwise>
            <xsl:text>h3</xsl:text>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:with-param>
    </xsl:apply-templates>
    <xsl:apply-templates select="ead:*[not(self::ead:head)]"/>
  </xsl:template>

  <!-- Render a heading with the passed in tag, or h3 as a default -->
  <xsl:template match="ead:head">
    <xsl:param name="heading_level" />
    <xsl:variable name="effective_level">
      <xsl:choose>
        <xsl:when test="$heading_level"><xsl:value-of select="$heading_level" /></xsl:when>
        <xsl:otherwise><xsl:text>h3</xsl:text></xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:element name="{$effective_level}">
      <xsl:value-of select="." />
    </xsl:element>
  </xsl:template>

  <!--
    Helper template to allow the use of IDs from EAD.

    IDs generated with generate-id() will be different between different
    renderings of the document.
  -->
  <xsl:template name="get_id">
    <xsl:param name="element" select="current()"/>
    <xsl:choose>
      <xsl:when test="$element[@id]">
        <xsl:value-of select="$element/@id"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="generate-id($element)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- Access to Reading room from the specific digital object reference id -->
 
   <!--url encode -->
  <xsl:template name="urlencode">
	<xsl:param name="paramdata" />
	<xsl:value-of select="php:function('urlencode', php:function('strip_tags', string($paramdata)))"/>
  </xsl:template>


  <xsl:template name="access_readingrm">
    <xsl:param name="element" select="current()"/>
    <xsl:if test= "$element[@id] != ''">
    <!-- define aeon parameters -->
    <xsl:variable name="EADnumber" select="/ead:ead/ead:eadheader/ead:eadid"/>
    <xsl:variable name="Callnumber" select="normalize-space(/ead:ead/ead:archdesc[@level='collection']/ead:did/ead:unitid[not(@*)])"/>
    <xsl:variable name="ItemAuthor" select="normalize-space(/ead:ead/ead:archdesc[@level='collection']/ead:did/ead:origination/ead:persname)"/>
    <xsl:variable name="ItemCitation" select="normalize-space(/ead:ead/ead:archdesc[@level='collection']/ead:prefercite/ead:p) "/>
    <xsl:variable name="ItemDate" select="normalize-space(ead:did/ead:unitdate)"/>
    <xsl:variable name="ItemInfo1" select="normalize-space(ancestor::*[local-name()='c' and @level='series'][1]/ead:did/ead:unittitle)"/>
    <xsl:variable name="ItemInfo2" 
select="(ead:accessrestrict/ead:p|ancestor::*[local-name()='c' and @level='series'][1]/ead:accessrestrict/ead:p|/ead:ead/ead:archdesc[@level='collection']/ead:accessrestrict/ead:p)[1]"/>
    <xsl:variable name="ItemNumber" select="substring-before(substring-after(ead:did/ead:container[@type='box']/@label,'['), ']')"/>
    <xsl:variable name="ItemTitle" select="normalize-space(/ead:ead/ead:archdesc[@level='collection']/ead:did/ead:unittitle)"/>
    <xsl:variable name="ItemSubTitle" select="normalize-space(concat(parent::*/ead:did/ead:unittitle, ' , ',parent::*/ead:did/ead:unitid[not(@*)]))"/>
    <xsl:variable name="ItemVolume" select="concat(ead:did/ead:container[not(@parent)]/@type,' ', ead:did/ead:container[not(@parent)])"/>
    <xsl:variable name="ItemIssue" select="concat(ead:did/ead:container[@parent]/@type,' ', ead:did/ead:container[@parent])"/>
    <xsl:variable name="readingroom_aeon">
                <xsl:value-of select="$base_aeon_url"/>
		<xsl:if test="$EADnumber != ''">
			<xsl:text>&amp;EADnumber=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$EADnumber" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="$Callnumber != ''">
			<xsl:text>&amp;CallNumber=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$Callnumber" />
			</xsl:call-template>
		</xsl:if>
		
		<!--collection name: Aeon label it as Location -->
		<xsl:if test="$ItemTitle != ''">
			<xsl:text>&amp;ItemTitle=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$ItemTitle" />
			</xsl:call-template>
		</xsl:if>

		<xsl:if test="$ItemSubTitle != ''">
			<xsl:text>&amp;ItemSubTitle=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$ItemSubTitle" />
			</xsl:call-template>
		</xsl:if>

		<xsl:if test="$ItemInfo1 != ''">
			<xsl:text>&amp;ItemInfo1=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$ItemInfo1" />
			</xsl:call-template>
		</xsl:if>

		<!--Restriction -->
		<xsl:text>&amp;ItemInfo2=</xsl:text>
                <xsl:if test="normalize-space($ItemInfo2) != ''">	
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$ItemInfo2" />
			</xsl:call-template>
		</xsl:if>
		
		<!-- barcode -->
		<xsl:if test="normalize-space($ItemNumber) != ''">
			<xsl:text>&amp;ItemNumber=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$ItemNumber" />
			</xsl:call-template>
		</xsl:if>

		<xsl:if test="$ItemAuthor != ''">
			<xsl:text>&amp;ItemAuthor=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$ItemAuthor" />
			</xsl:call-template>
		</xsl:if>
		
		<xsl:if test="$ItemCitation != ''">
			<xsl:text>&amp;ItemCitation=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$ItemCitation" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="$ItemDate != ''">
			<xsl:text>&amp;ItemDate=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$ItemDate" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="$ItemVolume != ''">
			<xsl:text>&amp;ItemVolume=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$ItemVolume" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="$ItemIssue != ''">
			<xsl:text>&amp;ItemIssue=</xsl:text>
			<xsl:call-template name="urlencode">
				<xsl:with-param name="paramdata" select="$ItemIssue" />
			</xsl:call-template>
		</xsl:if>        
	</xsl:variable> 

        <a class="astraeus-target">
          <xsl:attribute name="href">
            <xsl:value-of select="$readingroom_aeon"/>
          </xsl:attribute>
          <xsl:attribute name="target">_blank</xsl:attribute>
	  <xsl:attribute name="style">float: right;text-decoration: none!important;</xsl:attribute>
         <xsl:text>&#x1F56E; View in Reading Room</xsl:text>
        </a>
    </xsl:if>
  </xsl:template>  

  <!-- helper to transform a level attribute into a public-facing string -->
  <xsl:template name="decode_level">
    <xsl:param name="input" />
    <xsl:choose>
      <xsl:when test="$input = 'recordgrp'">
        <xsl:value-of select="$recordgrp_string" />
      </xsl:when>
      <xsl:when test="$input = 'subgrp'">
        <xsl:value-of select="$subgrp_string" />
      </xsl:when>
      <xsl:when test="$input = 'series'">
        <xsl:value-of select="$series_string" />
      </xsl:when>
      <xsl:when test="$input = 'subseries'">
        <xsl:value-of select="$subseries_string" />
      </xsl:when>
      <xsl:when test="$input = 'otherlevel'">
        <xsl:value-of select="$otherlevel_string" />
      </xsl:when>
      <xsl:when test="$input = 'subfonds'">
        <xsl:value-of select="$subfonds_string" />
      </xsl:when>
      <xsl:when test="$input = 'file'">
        <xsl:value-of select="$file_string" />
      </xsl:when>
      <xsl:when test="$input = 'item'">
        <xsl:value-of select="$item_string" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="capitalize">
          <xsl:with-param name="input" select="$input" />
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- General display -->
  <xsl:template match="ead:c | ead:c01 | ead:c02 | ead:c03 | ead:c04 | ead:c05 | ead:c06 | ead:c07 | ead:c08 | ead:c09">
    <fieldset>
      <xsl:attribute name="class">
        <xsl:text>ead-component collapsible </xsl:text>
        <!-- fieldsets without components are uncollapsed leaves; fieldsets with components default as collapsed -->
        <xsl:choose>
          <xsl:when test="not(ead:c | ead:c01 | ead:c02 | ead:c03 | ead:c04 | ead:c05 | ead:c06 | ead:c07 | ead:c08 | ead:c09)">
            <xsl:text>ead-leaf-fieldset </xsl:text>
          </xsl:when>
          <xsl:otherwise>
            <xsl:text>collapsed </xsl:text>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:value-of select="concat('ead-component-', local-name())"/>
        <xsl:text> </xsl:text>
        <xsl:value-of select="concat('ead-component-type-', @level)"/>
      </xsl:attribute>
      <legend>
        <span class="fieldset-legend">
          <xsl:if test="ead:did/ead:unitid[not(@type='aspace_uri')]">
            <span class="ead-legend-level">
              <xsl:if test="@level">
                <xsl:call-template name="decode_level">
                  <xsl:with-param name="input" select="@level" />
                </xsl:call-template>
                <xsl:text> </xsl:text>
              </xsl:if>
            </span>
            <span class="ead-legend-unitid">
              <xsl:value-of select="ead:did/ead:unitid[not(@type='aspace_uri')]" />
              <xsl:text> </xsl:text>
            </span>
          </xsl:if>
          <span class="ead-legend-title"><xsl:apply-templates select="ead:did/ead:unittitle"/></span>
          <xsl:if test="normalize-space(ead:did/ead:unittitle) and normalize-space(ead:did/ead:unitdate)"><xsl:text>, </xsl:text></xsl:if>
          <span class="ead-legend-date"><xsl:value-of select="ead:did/ead:unitdate"/></span>
        </span>
      </legend>
      <div class="fieldset-wrapper">
        <!-- This id should be on the fieldset semantically but is here to appease Drupal. -->
        <xsl:attribute name="id">
          <xsl:call-template name="get_id"/>
        </xsl:attribute>
        <xsl:call-template name="access_readingrm"/>
        <xsl:apply-templates/>
      </div>
    </fieldset>
  </xsl:template>

  <xsl:template match="ead:did">
    <xsl:variable name="contents">
      <xsl:call-template name="archdesc_did"/>
      <xsl:call-template name="eadheader"/>
      <xsl:call-template name="container"/>
    </xsl:variable>
    <xsl:if test="normalize-space($contents)">
      <dl class="ead-did-content">
        <xsl:copy-of select="$contents"/>
      </dl>
    </xsl:if>
    <xsl:if test="not(../../ead:archdesc)">
      <xsl:for-each select="ead:*[not(self::ead:dao) and not(self::ead:container) and not(self::ead:unitdate) and not(self::ead:unittitle) and not(self::ead:unitid)]">
        <xsl:choose>
        <xsl:when test="@label">
          <h3>
            <xsl:value-of select="@label" />
          </h3>
        </xsl:when>
        <xsl:otherwise>
          <h3>
            <xsl:call-template name="decode_did_child">
              <xsl:with-param name="input" select="local-name(.)" />
            </xsl:call-template>
          </h3>
        </xsl:otherwise>
        </xsl:choose>
        <p>
          <xsl:attribute name="class">
            <xsl:text>ead-</xsl:text><xsl:value-of select="local-name(.)" />
          </xsl:attribute>
          <xsl:apply-templates />
        </p>
      </xsl:for-each>
    </xsl:if>
      <xsl:if test="count(ead:dao[@xlink:href])">
        <xsl:variable name="xlinks">
          <xsl:call-template name="ead_dao_xlink" />
        </xsl:variable>
        <xsl:if test="normalize-space($xlinks)">
          <ul class="ead_daos">
            <xsl:copy-of select="$xlinks" />
          </ul>
        </xsl:if>
      </xsl:if>
  </xsl:template>

  <xsl:template match="ead:extent">
    <span class="ead-extent">
      <xsl:apply-templates />
    </span><xsl:text> </xsl:text>
  </xsl:template>

  <!-- helper to translate root did elements into defintion list names -->
  <xsl:template name="decode_did_child">
    <xsl:param name="input" />
    <xsl:choose>
      <xsl:when test="local-name(.) = 'unitid'">
        <xsl:value-of select="$unitid_string" />
      </xsl:when>
      <xsl:when test="local-name(.) = 'physdesc'">
        <xsl:value-of select="$physdesc_string" />
      </xsl:when>
      <xsl:when test="local-name(.) = 'physloc'">
        <xsl:value-of select="$physloc_string" />
      </xsl:when>
      <xsl:when test="local-name(.) = 'langmaterial'">
        <xsl:value-of select="$langmaterial_string" />
      </xsl:when>
      <xsl:when test="substring(local-name(.), 1, 4) = 'unit'">
        <xsl:call-template name="capitalize">
          <xsl:with-param name="input" select="substring(local-name(.), 5)" />
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="capitalize">
          <xsl:with-param name="input" select="local-name(.)" />
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- Handle top level did. -->
  <xsl:template name="archdesc_did">
    <xsl:if test="not(ead:container[@parent]) and ../../ead:archdesc">
      <xsl:for-each select="*[normalize-space(.) and not(@type='aspace_uri')]">
        <dt>
          <xsl:attribute name="class">
            <xsl:text>ead-</xsl:text><xsl:value-of select="local-name(.)" />
          </xsl:attribute>
          <xsl:choose>
            <xsl:when test="@label">
              <xsl:value-of select="@label" />
            </xsl:when>
            <xsl:otherwise>
              <xsl:call-template name="decode_did_child">
                <xsl:with-param name="input" select="local-name(.)" />
              </xsl:call-template>
            </xsl:otherwise>
          </xsl:choose>
        </dt>
        <dd>
          <xsl:attribute name="class">
            <xsl:text>ead-</xsl:text><xsl:value-of select="local-name(.)" />
          </xsl:attribute>
          <xsl:apply-templates select="."/>
        </dd>
      </xsl:for-each>
    </xsl:if>
  </xsl:template>

  <xsl:template name="eadheader">
    <xsl:if test="../../ead:archdesc">
      <xsl:for-each select="//ead:eadheader/ead:filedesc/ead:titlestmt/*[not(self::ead:titleproper)] | //ead:eadheader/ead:filedesc/ead:publicationstmt/*[not(self::ead:p)]">
        <dt>
          <xsl:attribute name="class">
            <xsl:text>ead-</xsl:text><xsl:value-of select="local-name(.)" />
          </xsl:attribute>
          <xsl:call-template name="capitalize">
            <xsl:with-param name="input" select="local-name(.)" />
          </xsl:call-template>
        </dt>
        <dd>
          <xsl:attribute name="class">
            <xsl:text>ead-</xsl:text><xsl:value-of select="local-name(.)" />
          </xsl:attribute>
          <xsl:apply-templates select="."/>
        </dd>
      </xsl:for-each>
    </xsl:if>
  </xsl:template>

  <xsl:template match="ead:address">
    <address>
      <xsl:apply-templates />
    </address>
  </xsl:template>

  <xsl:template match="ead:addressline">
    <xsl:apply-templates /><br />
  </xsl:template>

  <xsl:template match="ead:extptr">
    <xsl:choose>
      <xsl:when test="@xlink:href and @xlink:type = 'simple'">
        <a>
          <xsl:attribute name="href">
            <xsl:value-of select="@xlink:href" />
          </xsl:attribute>
          <xsl:choose>
            <xsl:when test="@xlink:title">
              <xsl:value-of select="@xlink:title" />
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="@xlink:href" />
            </xsl:otherwise>
          </xsl:choose>
        </a>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates />
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- build definition list containing container searches. -->
  <xsl:template name="container">
    <xsl:variable name="contents">
      <xsl:choose>
        <xsl:when test="ead:container[@parent]">
          <xsl:apply-templates select="ead:container[@parent]" mode="parent"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="flat_container"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:if test="normalize-space($contents)">
      <dt class="ead-container">
        <xsl:value-of select="$container_string"/>
      </dt>
      <xsl:copy-of select="$contents"/>
    </xsl:if>
  </xsl:template>

  <xsl:template name="flat_container">
    <dd class="ead-container ead-container-flat">
        <xsl:apply-templates select="ead:container[1]" mode="flat_text"/>
    </dd>
  </xsl:template>

  <xsl:template match="ead:container" mode="flat_text">
    <span>
    <xsl:attribute name="class">
      <xsl:text>container-target container-type-</xsl:text><xsl:value-of select="translate(@type, ' ', '-')" />
    </xsl:attribute>
    <xsl:value-of select="@type"/>
    <xsl:text> </xsl:text>
    <xsl:apply-templates/>
    </span>
    <xsl:variable name="sibling_content">
      <xsl:apply-templates select="following-sibling::ead:container[1]" mode="flat_text"/>
    </xsl:variable>
    <xsl:if test="normalize-space($sibling_content)">
      <xsl:text>, </xsl:text>
      <xsl:copy-of select="$sibling_content"/>
    </xsl:if>
  </xsl:template>

  <xsl:template match="ead:container" mode="parent">
    <xsl:variable name="containers" select="//ead:container"/>
    <dd class="ead-container ead-container-nested">
        <xsl:apply-templates select="." mode="parent_text"/>
    </dd>
  </xsl:template>

  <xsl:template match="ead:container" mode="parent_text">
    <xsl:variable name="parent" select="@parent"/>
    <xsl:variable name="parents">
      <xsl:apply-templates select="//ead:container[$parent = @id]" mode="parent_text"/>
    </xsl:variable>
    <xsl:if test="normalize-space($parents) != ''">
      <xsl:copy-of select="$parents"/>
      <xsl:text>, </xsl:text>
    </xsl:if>
    <span>
    <xsl:attribute name="class">
      <xsl:text>container-target container-type-</xsl:text><xsl:value-of select="translate(@type, ' ', '-')" />
    </xsl:attribute>
    <xsl:value-of select="@type"/>
    <xsl:text> </xsl:text>
    <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template name="ead_dao_xlink">
    <xsl:for-each select="ead:dao[@xlink:href]">
    <xsl:variable name="direct_url">
        <xsl:value-of select="$viewonlineuri"/><xsl:value-of select="@xlink:href" />
    </xsl:variable>
    <li>
      <xsl:choose>
      <xsl:when test="normalize-space($direct_url)">
        <a class="ead-external-link">
          <xsl:attribute name="href">
            <xsl:value-of select="$direct_url" />
          </xsl:attribute>
          <xsl:value-of select="ead:daodesc" />
        </a>
      </xsl:when>
      <xsl:otherwise>
      </xsl:otherwise>
        <xsl:value-of select="ead:daodesc" />
      </xsl:choose>
    </li>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="text()" mode="did_list"/>
  <!-- end of did/definition list stuff -->

  <xsl:template match="ead:dsc">
    <xsl:choose>
      <xsl:when test="normalize-space(ead:head)">
        <xsl:apply-templates select="ead:head">
          <xsl:with-param name="heading_level"><xsl:text>h2</xsl:text></xsl:with-param>
        </xsl:apply-templates>
      </xsl:when>
      <xsl:otherwise>
        <h2><xsl:value-of select="$containerlist_string" /></h2>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:apply-templates select="ead:*[not(self::ead:head)]" />
  </xsl:template>

  <!-- Structure controlled vocabularies as lists, but necessarily ignores non-list content -->
  <xsl:template match="ead:controlaccess">
    <xsl:if test="ead:corpname|ead:persname|ead:famname|ead:geogname|ead:occupation|ead:genreform|ead:cronlist|ead:function|ead:list|ead:name|ead:subject|ead:title|ead:controllaccess">
      <xsl:choose>
        <xsl:when test="normalize-space(ead:head)">
          <xsl:apply-templates select="ead:head">
            <xsl:with-param name="heading_level"><xsl:text>h1</xsl:text></xsl:with-param>
          </xsl:apply-templates>
        </xsl:when>
        <xsl:otherwise>
          <h2><xsl:value-of select="$controlaccess_string" /></h2>
        </xsl:otherwise>
      </xsl:choose>
        <xsl:if test="ead:corpname">
          <xsl:call-template name="corpnames" />
        </xsl:if>
        <xsl:if test="ead:persname">
          <xsl:call-template name="persnames" />
        </xsl:if>
        <xsl:if test="ead:famname">
          <xsl:call-template name="famnames" />
        </xsl:if>
        <xsl:if test="ead:geogname">
          <xsl:call-template name="geognames" />
        </xsl:if>
        <xsl:if test="ead:occupation">
          <xsl:call-template name="occupations" />
        </xsl:if>
        <xsl:if test="ead:genreform">
          <xsl:call-template name="genreforms" />
        </xsl:if>
        <xsl:if test="ead:cronlist|ead:function|ead:list|ead:name|ead:subject|ead:title">
          <xsl:call-template name="subjects" />
        </xsl:if>
      <xsl:if test="ead:controlaccess">
        <xsl:apply-templates select="ead:controlaccess" />
      </xsl:if>
    </xsl:if>
  </xsl:template>

  <!-- List each controlled vocabulary by type -->
  <xsl:template name="corpnames">
    <p><xsl:value-of select="$corpname_string" /></p>
    <ul>
    <xsl:for-each select="ead:corpname">
      <li><xsl:apply-templates /></li> 
    </xsl:for-each>
    </ul>
  </xsl:template>

  <xsl:template name="persnames">
    <p><xsl:value-of select="$persname_string" /></p>
    <ul>
    <xsl:for-each select="ead:persname">
      <li><xsl:apply-templates /></li>
    </xsl:for-each>
    </ul>
  </xsl:template>

  <xsl:template name="famnames">
    <p><xsl:value-of select="$famname_string" /></p>
    <ul>
    <xsl:for-each select="ead:famname">
      <li><xsl:apply-templates /></li>
    </xsl:for-each>
    </ul>
  </xsl:template>

  <xsl:template name="geognames">
    <p><xsl:value-of select="$geogname_string" /></p>
    <ul>
    <xsl:for-each select="ead:geogname">
      <li><xsl:apply-templates /></li>
    </xsl:for-each>
    </ul>
  </xsl:template>

  <xsl:template name="occupations">
    <p><xsl:value-of select="$occupation_string" /></p>
    <ul>
    <xsl:for-each select="ead:occupation">
      <li><xsl:apply-templates /></li>
    </xsl:for-each>
    </ul>
  </xsl:template>

  <xsl:template name="genreforms">
    <p><xsl:value-of select="$genreform_string" /></p>
    <ul>
    <xsl:for-each select="ead:genreform">
      <li><xsl:apply-templates /></li>
    </xsl:for-each>
    </ul>
  </xsl:template>

  <xsl:template name="subjects">
    <p><xsl:value-of select="$subject_string" /></p>
    <ul>
    <xsl:for-each select="ead:cronlist|ead:function|ead:list|ead:name|ead:subject|ead:title">
      <li><xsl:apply-templates /></li>
    </xsl:for-each>
    </ul>
  </xsl:template>

  <xsl:template match="ead:bibliography">
    <xsl:if test="count(ead:bibref)">
      <xsl:apply-templates select="ead:head">
        <xsl:with-param name="heading_level" select="h2" />
      </xsl:apply-templates>
      <ul>
        <xsl:for-each select="ead:bibref">
        <li>
          <xsl:apply-templates select="." />
        </li>
        </xsl:for-each>
      </ul>
    </xsl:if>
  </xsl:template>

  <xsl:template match="ead:list">
    <xsl:variable name="listtype">
      <xsl:choose>
        <xsl:when test="@type = 'ordered'">
          <xsl:text>ol</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>ul</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:if test="ead:head">
      <h1><xsl:value-of select="ead:head" /></h1>
    </xsl:if>
    <xsl:element name="{$listtype}">
      <xsl:for-each select="ead:item">
        <li><xsl:apply-templates select="." /></li>
      </xsl:for-each>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ead:p">
    <p class="ead-p">
      <xsl:apply-templates/>
    </p>
  </xsl:template>

  <xsl:template match="ead:extref">
    <a class="ead-external-link">
      <xsl:attribute name="href">
        <xsl:value-of select="@xlink:href"/>
      </xsl:attribute>
      <xsl:value-of select="text()"/>
    </a>
  </xsl:template>

  <xsl:template match="ead:emph[@render='italic']|ead:title[@render='italic']">
   <i><xsl:apply-templates select="node()"/></i>
  </xsl:template>
  <!-- end of general display stuff -->

  <xsl:template name="capitalize">
    <xsl:param name="input" />
    <xsl:variable name="lowerchars" select="'abcdefghijklmnopqrstuvwxyz'" />
    <xsl:variable name="upperchars" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
    <xsl:value-of select="concat(translate(substring($input, 1, 1), $lowerchars, $upperchars), substring($input, 2))" />
  </xsl:template>

</xsl:stylesheet>
