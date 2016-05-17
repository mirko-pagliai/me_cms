<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?php
	//Adds tags as keywords meta-tag
	if(config('post.keywords') && $this->request->isAction('view', 'Posts') && !empty($post->tags_as_string)) {
		$this->Html->meta('keywords', preg_replace('/,\s/', ',', $post->tags_as_string));
    }
?>

<div class="post-container content-container">
	<div class="content-header">
		<?php
			if(config('post.category') && !empty($post->category->title) && !empty($post->category->slug)) {
				echo $this->Html->h5($this->Html->link($post->category->title, ['_name' => 'posts_category', $post->category->slug]), ['class' => 'content-category']);
            }
            
			echo $this->Html->h3($this->Html->link($post->title, ['_name' => 'post', $post->slug]), ['class' => 'content-title']);

			if(!empty($post->subtitle)) {
				echo $this->Html->h4($this->Html->link($post->subtitle, ['_name' => 'post', $post->slug]), ['class' => 'content-subtitle']);
            }
		?>
        
		<div class="content-info">
			<?php
				if(config('post.author') && !empty($post->user->full_name)) {
					echo $this->Html->div('content-author',
						__d('me_cms', 'Posted by {0}', $post->user->full_name),
						['icon' => 'user']
					);
                }
                
				if(config('post.created') && !empty($post->created)) {
					echo $this->Html->div('content-date',
						__d('me_cms', 'Posted on {0}', $post->created->i18nFormat(config('main.datetime.long'))),
						['icon' => 'clock-o']
					);
                }
			?>
		</div>
	</div>
    
	<div class="content-text">
		<?php
			//Executes BBCode on the text
			$post->text = $this->BBCode->parser($post->text);
			
			//Truncates the text if the "<!-- read-more -->" tag is present
			if(!$this->request->isAction('view', 'Posts') && $strpos = strpos($post->text, '<!-- read-more -->')) {
				echo $truncated_text = $this->Text->truncate($post->text, $strpos, ['ellipsis' => FALSE, 'exact' => TRUE, 'html' => FALSE]);
            }
			//Truncates the text if requested by the configuration
			elseif(!$this->request->isAction('view', 'Posts') && config('frontend.truncate_to')) {
				echo $truncated_text = $this->Text->truncate($post->text, config('frontend.truncate_to'), ['exact' => FALSE, 'html' => TRUE]);
            }
			else {
				echo $post->text;
            }
		?>
	</div>
    
	<div class="content-buttons">
		<?php
			//If it was requested to truncate the text and that has been truncated, it shows the "Read more" link
			if(!empty($truncated_text) && $truncated_text !== $post->text) {
				echo $this->Html->button(__d('me_cms', 'Read more'), ['_name' => 'post', $post->slug], ['class' => ' readmore']);
            }
		?>
	</div>
    
    <?php
        if(config('post.tags') && !empty($post->tags) && $this->request->isAction('view', 'Posts') && !$this->request->isAjax()) {
            echo $this->Html->div('content-tags', implode(PHP_EOL, array_map(function($tag) {
                return $this->Html->link($tag->tag, ['_name' => 'posts_tag', $tag->slug], ['icon' => 'tags']);
            }, $post->tags)));
        }
    ?>
    
	<?php
		if(config('post.shareaholic') && config('shareaholic.app_id')) {
			if($this->request->isAction('view', 'Posts') && !$this->request->isAjax()) {
				echo $this->Html->shareaholic(config('shareaholic.app_id'));
            }
        }
	?>
</div>

<?php if(!empty($related)): ?>
	<div class="related-contents">
		<?= $this->Html->h4(__d('me_cms', 'Related posts')) ?>
		<?php if(!config('post.related.images')): ?>
			<?= $this->Html->ul(array_map(function($post) {
					return $this->Html->link($post->title, ['_name' => 'post', $post->slug]);
				}, $related), ['icon' => 'caret-right']) ?>
		<?php else: ?>
			<div class="visible-xs">
				<?= $this->Html->ul(array_map(function($post) {
					return $this->Html->link($post->title, ['_name' => 'post', $post->slug]);
				}, $related), ['icon' => 'caret-right']) ?>
			</div>
		
			<div class="hidden-xs row">
				<?php foreach($related as $post): ?>
					<div class="col-sm-6 col-md-3">
						<?= $this->element('frontend/views/post-preview', compact('post')) ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>