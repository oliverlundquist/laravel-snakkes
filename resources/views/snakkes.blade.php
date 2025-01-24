<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
        <link rel="shortcut icon" href="/favicon/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
        <link rel="manifest" href="/favicon/site.webmanifest" />
        <title>Snakkes 2025</title>
        <script src="/js/alpine.min.js" defer></script>
        <script src="/js/d3.min.js"></script>
        <link rel="stylesheet" href="/css/bulma.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bungee&display=swap" rel="stylesheet">
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
        <link rel="manifest" href="/favicon/site.webmanifest">
        <style>
            body {
                font-family: "Bungee", serif;
                font-weight: 400;
                font-style: normal;
            }
            [x-cloak] {
                display: none !important;
            }
        </style>
    </head>
    <body>
        <div class="container" x-data>
            <div class="columns is-centered is-vcentered is-mobile">
                <div class="column is-narrow has-text-centered">
                    <h1 class="mb-0" style="font-size: 6rem; line-height: 6rem">Snakkes</h1>
                    <template x-if="$store.game.state === 'running'">
                        <div class="mb-3">
                            <h2 class="is-size-4">Game Score</h2>
                            <div x-text="'You: ' + $store.game.scores.player1 + ' CPU: ' + $store.game.scores.player2"></div>
                        </div>
                    </template>
                    <template x-if="$store.game.state === 'finished'">
                        <div class="mb-3">
                            <h2 class="is-size-4" x-text="$store.game.scores.player1 > $store.game.scores.player2 ? 'Game Over, You won!' : 'Game Over, You lost!'"></h2>
                            <h4 class="is-size-6" x-text="'Difficulty: ' + $store.game.difficulty + ', Score: ' + $store.game.scores.player1 + ' to ' + $store.game.scores.player2"></h4>
                        </div>
                    </template>
                    <template x-if="$store.game.state !== 'running'">
                        <div>
                            <div class="radios is-justify-content-center">
                                <label class="radio">
                                    <input x-model="$store.state.difficulty" value="normal" type="radio" name="difficulty" />
                                    Normal
                                </label>
                                <label class="radio">
                                    <input x-model="$store.state.difficulty" value="insane" type="radio" name="difficulty" />
                                    Insane
                                </label>
                            </div>
                            <button @click="sendStartGameMessage()" class="mb-3 button is-light is-medium">Start Game</button>
                        </div>
                    </template>
                    <div x-cloak x-show="$store.game.state !== ''">
                        <div id="game"></div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // ////// //
            // SOCKET //
            // ////// //
            let socket         = openSocket(null, socketMessage, socketClose);
            const messageQueue = [];

            function socketMessage(event) {
                const payload = JSON.parse(event.data);

                if (payload.event === 'game_data') {
                    const game  = Alpine.store('game');
                    game.state  = payload.state;
                    game.scores = payload.scores;
                    Alpine.store('game', game);
                    repaint(payload.board);
                }

                if (payload.event === 'welcome_message') {
                    const state         = Alpine.store('state');
                    state.connection_id = payload.connection_id;
                    state.instance_name = payload.instance_name;
                    state.ready         = true;
                    Alpine.store(state, state);
                }

                if (payload.event === 'user_list') {
                    Alpine.store('users', payload.users);
                }
            }
            function socketClose(event) {
                socket = openSocket(Alpine.store('state').connection_id, socketMessage, socketClose);
            }
            function openSocket(connectionId, onMessage, onClose) {
                const url = (typeof connectionId === 'string' && connectionId.length === 36)
                    ? 'ws://0.0.0.0?connectionId=' + connectionId
                    : 'ws://0.0.0.0';
                const webSocket     = new WebSocket(url);
                webSocket.onmessage = onMessage;
                webSocket.onclose   = onClose;
                return webSocket;
            }
            // ////////// //
            // GAME LOGIC //
            // ////////// //
            document.addEventListener('keydown', (event) => {
                switch(event.which) {
                    case 37: // left
                        sendDirectionMessage('left');
                        break;
                    case 38: // up
                        sendDirectionMessage('up');
                        break;
                    case 39: // right
                        sendDirectionMessage('right');
                        break;
                    case 40: // down
                        sendDirectionMessage('down');
                        break;
                    default: return; // exit this handler for other keys
                }
                event.preventDefault(); // prevent the default action (scroll / move caret)
            });
            (function paint() {
                const boardWidth      = {{ $board->width }};
                const boardHeight     = {{ $board->height }};
                const squareWidth     = {{ $board->squareWidth }};
                const squareHeight    = {{ $board->squareHeight }};
                const ticksX          = JSON.parse("{{ json_encode($board->ticksX) }}");
                const ticksY          = JSON.parse("{{ json_encode($board->ticksY) }}");
                const backgroundColor = "{{ $board->backgroundColor }}";

                d3.select("#game")
                        .append("svg")
                        .attr("width", boardWidth)
                        .attr("height", boardHeight)
                        .append("g")
                        .attr("id", "board");

                ticksX.map(function (xTick) {
                    ticksY.map(function (yTick) {
                        d3.select("#board")
                            .append("rect")
                            .attr("x", xTick)
                            .attr("y", yTick)
                            .attr("width", squareWidth)
                            .attr("height", squareHeight)
                            .attr("class", "pixies")
                            .attr("fill", backgroundColor);
                    });
                });
            })();
            function repaint(paintedCoordinates) {
                const backgroundColor = "{{ $board->backgroundColor }}";

                d3.selectAll('#board .pixies').each(function () {
                    var _this = d3.select(this);
                    var x     = parseInt(_this.attr('x'), 10);
                    var y     = parseInt(_this.attr('y'), 10);
                    var fill  = backgroundColor;

                    paintedCoordinates.forEach(function (coordinates) {
                        if (coordinates[0] === x && coordinates[1] === y) {
                            fill = coordinates[2];
                        }
                    });
                    _this.attr('fill', fill);
                });
            };
            function sendDirectionMessage(direction) {
                const payload = {
                    event: 'direction_change',
                    app_name: 'snakkes',
                    message: direction
                }
                messageQueue.push(JSON.stringify(payload));
            }
            function sendStartGameMessage() {
                const state = Alpine.store('state');
                const game  = Alpine.store('game');

                // set game difficulty
                game.difficulty = state.difficulty;
                Alpine.store('game', game);

                const payload = {
                    event: 'start_game',
                    app_name: 'snakkes',
                    message: state.difficulty
                }
                messageQueue.push(JSON.stringify(payload));
            }
            function sendPingMessage()
            {
                const payload = {
                    event: 'ping',
                    app_name: 'snakkes',
                    message: 'ping!'
                }
                messageQueue.push(JSON.stringify(payload));
            }
            // ///// //
            // LOOPS //
            // ///// //
            const pingTimer = setInterval(() => {
                if (Alpine.store('state').ready !== true) {
                    return;
                }
                if (Alpine.store('game').state === 'running') {
                    return;
                }
                sendPingMessage();
            }, 5000);
            const messageQueueTimer = setInterval(() => {
                if (Alpine.store('state').ready !== true) {
                    return;
                }
                if (socket.bufferedAmount > 0) {
                    return;
                }
                if (messageQueue.length === 0) {
                    return;
                }
                const nextMessage = messageQueue.shift();
                socket.send(nextMessage);
            }, 80);
            // ///////////// //
            // DEFAULT STATE //
            // ///////////// //
            document.addEventListener('alpine:init', () => {
                Alpine.store('state', { ready: false, difficulty: 'normal' });
                Alpine.store('game', { state: '', scores: {}, difficulty: '' });
                Alpine.store('users', []);
            });
        </script>
    </body>
</html>
