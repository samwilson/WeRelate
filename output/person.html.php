
<div class="person infobox main">
<span class="image"><img src="" /></span>
<h2 class="title"><?php echo $person->getFullName() ?></h2>
<ul class="bmd">
    <li>
        b. <?php echo $person->getBirthDate() ?>
        <?php echo $parser->recursiveTagParse('[[Place:'.$person->getBirthPlace().']]', $frame) ?>
    </li>
    <li>
        d. <?php echo ($person->getDeathDate()) ? $person->getDeathDate() : wfMessage('Unknown')->parse() ?>
        <?php if ($person->getDeathPlace()) echo $parser->recursiveTagParse('[[Place:'.$person->getDeathPlace().']]', $frame) ?>
    </li>
</ul>
</div>


<h3>Parents and siblings</h3>
<?php foreach ($person->getFamilies('child') as $parentFam): ?>
    <?php $parentFam->load(); if (!$parentFam->pageExists()): ?>
    <p><a href="">Create <?php echo $parentFam->getTitle() ?></a></p>
    <?php else: ?>
    <ul>
    <li>
		F. <?php if ($f = $parentFam->getSpouse('husband')) {
			echo Linker::link($f->getTitle(), $f->getFullName());
			echo ' '.$f->getBirthDate().'&ndash;'.$f->getDeathDate();
		} ?>
    </li>
    <li>
		M. <?php if ($w = $parentFam->getSpouse('wife')) {
			echo Linker::link($w->getTitle(), $w->getFullName());
			echo ' '.$w->getBirthDate().'&ndash;'.$w->getDeathDate();
		} ?>
	</li>
    <li>Children:<ol>
        <?php foreach ($parentFam->getChildren() as $sibling): ?>
        <li>
            <?php if ((string)$sibling->getTitle()==(string)$person->getTitle()) echo $sibling->getFullName();
            else echo Linker::link($sibling->getTitle(), $sibling->getFullName()); ?>
        </li>
        <?php endforeach ?>
    </ol></li></ul>
    <?php endif ?>
<?php endforeach ?>


<?php foreach ($person->getFamilies('spouse') as $family): ?>
    <div class="family">
    <h3>Spouse and children</h3>
    <ul>
    <li>H. <?php if ($h=$family->getSpouse('husband')) echo Linker::link($h->getTitle(), $h->getFullName()); ?></li>
    <li>W. <?php if ($w=$family->getSpouse('wife')) echo Linker::link($w->getTitle(), $w->getFullName()); ?></li>
    <ol>
        <?php foreach ($family->getChildren() as $child): ?>
        <li>
            <?php if ((string)$child->getTitle()==(string)$person->getTitle()) echo $child->getFullName();
            else echo Linker::link($child->getTitle(), $child->getFullName()); ?>
        </li>
        <?php endforeach ?>
    </ol>
    </div>
<?php endforeach ?>

<h3>Facts</h3>
<table class="wikitable">
<?php foreach ($person->getFacts() as $fact): ?>
<tr>
<th><?php echo $fact['type'] ?><?php 
foreach ($fact['sources'] as $source_id) {
    if ($source = $person->getSource($source_id)) {
		$title = $source['title'];
		if ($title = Title::newFromText($title)) { 
			$is_source = $title->getNamespace() == NS_WERELATECORE_SOURCE;
			$is_mysource = $title->getNamespace() == NS_WERELATECORE_MYSOURCE;
			if ($is_source || $is_mysource) $title = '[['.$source['title'].']]';
        }
        echo $this->parser->recursiveTagParse('<ref name="'.$source_id.'">'.$title.' '.$source['body'].'</ref>');
    }
} ?></th>
<td><span title="<?php echo $fact['sortDate'] ?>"><?php echo $fact['date'] ?></span></td>
<td><?php echo $parser->recursiveTagParse('[[Place:'.$fact['place'].']]', $frame) ?></td>
<td><?php echo $fact['desc'] ?></td>
</tr>
<?php endforeach ?>
</table>

