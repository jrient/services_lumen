<header>
    <div class="container">
        <nav class="mobile-nav show-on-mobiles">
            <h2>Geek时间分享</h2>
        </nav>
    </div>
</header>
<div class="container-fluid">
    <div class="row col-md-12">
        <div class="col-md-9" role="main">
            <div>
                <h3 id="article_title"></h3>
                <br>
                <h5 id="article_summary"></h5>
                <div id="media" class="hidden">
                    <br>
                    <audio controls="controls">
                        <source id="audio" src="http://m10.music.126.net/20181118234340/1bf5a5313f54758d7b95f84a882537a1/ymusic/5c79/df47/b803/9664c22c116603fb6fbd11a7823b9328.mp3" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>
                <hr>
            </div>
            <div id="content"></div>
        </div>
        <div class="col-md-3" role="complementary">
            <ul>
            <?php foreach ($data['data'] as $category => $list):?>
                    <li>
                        <?= $category ?>
                        <ul>
                            <?php foreach ($list as $item):?>
                                <li>
                                    <a href="javascript:void(0)" data-toggle="collapse" data-target="#book_<?= $item->book_id ?>"><?= $item->title ?></a>
                                    <ul id="book_<?= $item->book_id ?>" class="panel-collapse collapse">
                                        <?php foreach ($item->article_info as $i):?>
                                            <li title="<?= $i->article_summary ?>">
                                                <a href="javascript:void(0)" onclick="loadContent(<?= $i->article_id ?>)"><?= $i->article_title ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
            <?php endforeach; ?>
                <li>
                    <a href="/geekbang/provider">贡献cookie</a>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    var loadContent = function(articleId) {
        $.ajax({
            type: 'get',
            data: {id:articleId},
            url: '/geekbang/article',
            success: function(data){
                var article = data.data;
                $('#article_title').html(article.article_title);
                $('#article_summary').html(article.article_summary);
                $('#content').html(article.content);
                var media = $('#media');
                if (article.audio_download_url !== '') {
                    media.removeClass('hidden');
                    var audio = $('#audio')[0];
                    audio.setAttribute('src',article.audio_download_url);
                    audio.parentElement.load();
                } else {
                    media.addClass('hidden');
                }
            }
        });
        audio.parentElement.load();
    }
</script>