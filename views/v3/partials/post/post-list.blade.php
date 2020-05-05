
<ul class="archive-list">

       @foreach($posts as $post)
        
        <li>
            @typography(['variant' => 'h2'])
                {{$post->postTitle}}
            @endtypography

            
            @typography(['variant' => 'p', 'element' => 'p'])
                {{$post->excerpt}}
            @endtypography
            
            @typography(['variant' => 'meta'])

                @link([
                    'href' =>  $post->permalink
                ])
                    {{$post->permalink}}
                @endlink

            @endtypography

            @if (get_field('archive_' . sanitize_title(get_post_type()) . '_feed_date_published', 'option') != 'false')
  
                @typography(['variant' => 'meta'])
              
                    @date([
                        'action' => 'formatDate',
                        'timestamp' =>  $post->postDate
                    ])
                    @enddate

                @endtypography

            @endif
            
        </li>

        
       @endforeach
</ul>
  
