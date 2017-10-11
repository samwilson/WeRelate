<div class="infobox">
<table>
    <tr>
        <th>MySource</th>
        <td><?php echo $this->getTitle()->getSubpageText() ?></td>
    </tr>
    <tr>
        <th>Author</th>
        <td><?php echo $this->getAuthor() ?></td>
    </tr>
    <tr class="separator">
        <th>Coverage</th>
        <td></td>
    </tr>
    <tr>
        <th>Year range</th>
        <td>
            <?php if (!empty($this->xml->from_year.$this->xml->to_year)): ?>
                <?php echo $this->xml->from_year ?> &ndash; <?php echo $this->xml->to_year ?>
            <?php endif ?>
        </td>
    </tr>
    <tr>
        <th>Surnames</th>
        <td>
            <ul>
            <?php foreach ( $this->getSurnames() as $surname ): ?>
                <li><?php echo $parser->recursiveTagParse( "[[Surname:$surname|$surname]]" ) ?></li>
            <?php endforeach ?>
            </ul>
        </td>
    </tr>
    <tr>
        <th>Places</th>
        <td>
            <ul>
                <?php foreach ( $this->xml->place as $place ): ?>
                    <li><?php echo $parser->recursiveTagParse( "[[Place:$place|$place]]" ) ?></li>
                <?php endforeach ?>
            </ul>
        </td>
    </tr>
    <tr class="separator">
        <th>Publication information</th>
        <td></td>
    </tr>
    <tr>
        <th>Publication</th>
        <td><?php echo $parser->recursiveTagParse( $this->xml->publication_info ) ?></td>
    </tr>
    <tr>
        <th>URL</th>
        <td><?php echo $parser->recursiveTagParse( $this->xml->url ) ?></a></td>
    </tr>
    <tr class="separator">
        <th>Citation</th>
        <td></td>
    </tr>
    <tr>
        <td colspan="2">
            <?php if ( $this->getAuthor() ) echo $this->getAuthor() . '.' ?>
            <?php echo '<em>' . $this->getTitle()->getSubpageText() . '</em>.' ?>
            <?php if ( $this->getPublicationInfo() ) echo '(' . $this->getPublicationInfo() . ').' ?>
        </td>
    </tr>
</table>
</div>
