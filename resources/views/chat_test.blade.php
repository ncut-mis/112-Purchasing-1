<!DOCTYPE html>
<html>
<head>
    <title>Pusher 即時通訊測試</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>
<body>
    <h1>即時聊天測試</h1>
    <div id="messages" style="border: 1px solid #ccc; height: 200px; overflow-y: scroll; padding: 10px; margin-bottom: 10px;">
        </div>

    <script>
        // 開啟 Debug 模式，可以在瀏覽器按 F12 看連線紀錄
        Pusher.logToConsole = true;

        // 2. 初始化 Pusher (請填入你的 KEY)
        var pusher = new Pusher('712939e452b4d0a10402', {
            cluster: 'ap3',
            forceTLS: true
        });

        // 3. 訂閱我們之前在 Event 寫好的頻道 'chat-channel'
        var channel = pusher.subscribe('chat-channel');

        // 4. 監聽事件 'message.sent'
        channel.bind('message.sent', function(data) {
            var messagesDiv = document.getElementById('messages');
            var newMessage = document.createElement('p');
            
            // 顯示收到的人名與內容
            newMessage.innerHTML = "<strong>" + data.userName + "：</strong>" + data.messageContent;
            messagesDiv.appendChild(newMessage);
            
            // 自動捲到底部
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        });
    </script>
</body>
</html>