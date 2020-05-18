<div class="search-result-item u-margin__bottom--4">
    @typography(['variant' => 'h4', 'element' => 'h4'])
        @link([
            'href' => $result['topMostPostParent']->href,
            'classList' => ['search-result-item__parent-title-link']
        ])
            {{$result['topMostPostParent']->post_title}}
        @endlink
        
          
    @endtypography

    @typography(['variant' => 'h3', 'element' => 'h3'])
        @link([
            'href' => $result['permalink'],
            'classList' => ['search-result-item__parent-title-link']
        ])
            {{$result['postParent']->post_title}}
        @endlink
        /
        @link([
            'href' => $result['permalink'],
            'classList' => ['search-result-item__title-link']
        ])
            {{$result['title']}}
        @endlink
          
    @endtypography
 
    <p style="display: inline-block;">
        @if($result['featuredImage'])
            <img src="{{$result['featuredImage']}}">
        @endif
        {{$result['excerpt']}}
    </p>



    @typography(['variant' => 'caption'])
        <span class="search-result-item__link-prefix"> 
            Link: 
        </span>
        @link([
            'href' => $result['permalink']
        ])
            {{$result['permalink']}}
        @endlink

    @endtypography
</div>





