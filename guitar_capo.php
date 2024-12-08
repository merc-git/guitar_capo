<?php

// ギターコードの音を順番に並べた配列（メジャー、マイナー、セブンスなどを含む）
$notes = [
    'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 
    'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm',
    'Cmaj7', 'Dmaj7', 'Emaj7', 'Fmaj7', 'Gmaj7', 'Amaj7', 'Bmaj7',
    'C7', 'D7', 'E7', 'F7', 'G7', 'A7', 'B7'
];

// コードをカポの位置に応じて変換する関数
function transposeChord($chord, $currentCapo, $newCapo) {
    global $notes;

    // カポ位置の差を計算
    $capoDifference = $newCapo - $currentCapo;

    // コードのインデックスを探す（前後の空白を除去して検索）
    $chord = trim($chord);

    // コードのインデックスを探す
    $chordIndex = array_search($chord, $notes);

    if ($chordIndex === false) {
        return "無効なコードです"; // 配列に見つからなかった場合
    }

    // 新しいコードのインデックスを計算
    $newChordIndex = ($chordIndex + $capoDifference) % count($notes);

    // マイナスになった場合は回転させる
    if ($newChordIndex < 0) {
        $newChordIndex += count($notes);
    }

    // 新しいコードを返す
    return $notes[$newChordIndex];
}

// コード進行を変換する関数
function transposeChordProgression($progression, $currentCapo, $newCapo) {
    // 改行を維持するために入力を行ごとに分割
    $lines = explode("\n", $progression);

    // 変換されたコード進行を格納する配列
    $newLines = [];

    // 各行のコードを変換
    foreach ($lines as $line) {
        // 行のコードをスペースで分割
        $chords = explode(' ', $line);
        $newChords = [];

        // 各コードを変換
        foreach ($chords as $chord) {
            $newChords[] = transposeChord($chord, $currentCapo, $newCapo);
        }

        // 新しいコード進行を結合して新しい行として追加
        $newLines[] = implode(' ', $newChords);
    }

    // 新しいコード進行を改行で結合して返す
    return implode("\n", $newLines);
}

// フォームから送信されたデータを処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームデータの取得
    $newCapo = $_POST['newCapo'];          // 変換後のカポ位置
    $lyricsAndChords = $_POST['lyricsAndChords'];  // 入力された歌詞とコード進行

    // デフォルトのカポ位置
    $currentCapo = 0;  // 現在のカポ位置

    // 歌詞とコード進行を分ける
    $lines = explode("\n", $lyricsAndChords);
    $newLyricsAndChords = [];

    foreach ($lines as $line) {
        // 歌詞とコード進行を分ける
        preg_match_all('/[A-Ga-g#m]+(maj7|7|m|#|b|maj)*\b/', $line, $matches); // コード進行を検出
        
        // コード進行を変換
        $newLine = $line;
        foreach ($matches[0] as $chord) {
            $newChord = transposeChord($chord, $currentCapo, $newCapo);
            $newLine = str_replace($chord, $newChord, $newLine);
        }

        // 変換後の歌詞とコード進行を新しい行として追加
        $newLyricsAndChords[] = $newLine;
    }

    // 新しい歌詞とコード進行を改行で結合して返す
    $newLyricsAndChords = implode("\n", $newLyricsAndChords);
} else {
    // 初回表示
    $lyricsAndChords = '';
    $newLyricsAndChords = '';
    $newCapo = 2;
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>カポ変換フォーム</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }


        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            font-family: Arial, sans-serif;
            background-image: url('back.jpg'); 
 background-size: cover;               
   background-attachment: fixed;         
   background-position: center center;   
        }
        .container {
            padding: 30px; 
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-align: center;
            width: 100%;
            max-width: 1000px; 
            box-sizing: border-box;
        }
        h2 {
            color: #333;
        }
        label {
            font-size: 1.3em; 
        }
        textarea, input[type="number"], input[type="submit"] {
            padding: 15px; 
            margin: 15px 0;
            width: 100%;
            box-sizing: border-box;
            font-size: 1.2em; 
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        textarea {
            height: 300px; 
            resize: vertical;
            rows: 15; 
            cols: 50; 
        }
        input[type="submit"] {
            padding: 15px 25px;
            font-size: 1.2em;
            background-color: #87cefa;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #87cefa;
        }
        .result {
            margin-top: 30px;
            width: 100%;
        }
        .result textarea {
            height: 300px; 
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
            resize: vertical;
            rows: 15;
            cols: 50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ギターコード　カポ変換</h2>
        <form method="POST">
            <label for="lyricsAndChords">テキストで入力したコード進行を変換します（歌詞付きにも対応）</label><br>
            <textarea name="lyricsAndChords" rows="15" cols="50" required><?= htmlspecialchars($lyricsAndChords) ?></textarea><br>
            <label for="newCapo">capo:</label><br>
            <input type="number" name="newCapo" value="<?= $newCapo ?>" min="0" max="12" required><br>
            <input type="submit" value="変換">
        </form>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <div class="result">
            <h3>変換結果</h3>
            <textarea readonly rows="15" cols="50"><?= htmlspecialchars($newLyricsAndChords) ?></textarea>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
