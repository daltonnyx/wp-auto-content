<?php global $post; ?>
<p>
  <label for="<?php echo $this->name ?>"><?php echo $this->caption; ?></label>
  <br />
  <textarea class="widefat" type="text" name="<?php echo $this->name ?>" id="<?php echo $this->name ?>"
    rows="7"><?php echo get_post_meta( $post->ID, '_vnb_' . $this->name, true ); ?></textarea>
</p>
