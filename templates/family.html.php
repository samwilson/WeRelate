<div class="infobox">
    <?php foreach (['husband', 'wife' ] as $spouseType ): ?>
        <?php $spouse = $this->getSpouse( $spouseType ); if (!$spouse) continue; ?>
        <div class="spouse spouse-<?php echo $spouseType ?>">
            <p class="parents">
                Parents:
                <?php
                $parentFamName = (string)$this->xml->{$spouseType}['child_of_family'];
                echo $parser->recursiveTagParse("[[Family:$parentFamName]]");
                ?>
            </p>
            <h1>
                <?php echo $parser->recursiveTagParse(
                    '[[Person:' . $spouse->getFullName() . '|' . $spouse->getFullName() . ']]'
                ) ?>
            </h1>
            <ul>
                <li>
                    Born: <?php echo $spouse->getBirthDate() ?>
                    <?php echo $parser->recursiveTagParse(
                            '[[Place:' . $spouse->getBirthPlace() . ']]'
                    ) ?>
                </li>
                <li>
                    Died: <?php echo $spouse->getDeathDate() ?>
                    <?php echo $parser->recursiveTagParse(
                        '[[Place:' . $spouse->getDeathPlace() . ']]'
                    ) ?>
                </li>
            </ul>
        </div>
    <?php endforeach ?>
</div>

<pre><?php print_r($this->xml) ?></pre>
