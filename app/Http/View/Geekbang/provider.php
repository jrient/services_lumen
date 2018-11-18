
<textarea id="cookie" class="form-control" rows="10" placeholder="cookie"></textarea>
<button class="btn btn-info" onclick="sub()">提交</button>

<script>
    var sub = function(){
        var cookie = $("#cookie").val();
        $.ajax({
            type: 'post',
            data: {cookie:cookie},
            url: '/geekbang/cookie',
            success: function(){
                $.ajax({
                    type: 'get',
                    url: '/geekbang/updateData',
                    success: function(){}
                });
                alert('ThankU,系统将立即进行数据的同步，请稍后前往主页查看');
            }
        });
    }
</script>