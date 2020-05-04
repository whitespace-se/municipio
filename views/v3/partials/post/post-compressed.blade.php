<div class="archive-compressed">
    @foreach($posts as $post)
        @includeIf('partials.article', 
            [
                'postTitleFiltered' => $post->postTitle, 
                'postContentFiltered' => $post->postContent, 
                'permalink' => $post->permalink,
                'feature_image' => 
                (object) [
                    'src' => $post->featuredimage, 
                    'alt' => 'ad', 
                    'title' => 'asd'
                ]
            ]
        )
    @endforeach
</div>