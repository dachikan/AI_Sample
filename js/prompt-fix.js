/**
 * プロンプト表示の修正用スクリプト
 * プロンプトテキストの整形とコピー機能を提供します
 */
document.addEventListener("DOMContentLoaded", () => {
  // プロンプトコンテナを取得
  const promptContainers = document.querySelectorAll(".prompt-container")

  // 各プロンプトコンテナに対して処理
  promptContainers.forEach((container) => {
    // プロンプトテキストを整形
    formatPromptText(container)

    // コピーボタンの機能を追加
    setupCopyButton(container)
  })

  // 画像モーダルの設定
  setupImageModal()
})

/**
 * プロンプトテキストを整形する関数
 * コードブロック、リンク、リスト等を適切に表示
 */
function formatPromptText(container) {
  // プロンプトテキスト要素を取得
  const promptText = container.querySelector("#promptText")
  if (!promptText) return

  let content = promptText.innerHTML

  // コードブロックを検出して整形
  content = content.replace(
    /```([a-z]*)\n([\s\S]*?)```/g,
    (match, language, code) => `<pre><code class="language-${language}">${escapeHtml(code)}</code></pre>`,
  )

  // インラインコードを検出して整形
  content = content.replace(/`([^`]+)`/g, "<code>$1</code>")

  // URLをリンクに変換
  content = content.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>')

  // リストを検出して整形
  content = content.replace(/^\s*[-*]\s+(.+)$/gm, "<li>$1</li>")
  content = content.replace(/(<li>.*<\/li>)/gs, "<ul>$1</ul>")

  // 番号付きリストを検出して整形
  content = content.replace(/^\s*(\d+)\.\s+(.+)$/gm, "<li>$2</li>")
  content = content.replace(/(<li>.*<\/li>)/gs, "<ol>$1</ol>")

  // 見出しを検出して整形
  content = content.replace(/^#{1,6}\s+(.+)$/gm, (match, text) => {
    const level = match.trim().indexOf(" ")
    return `<h${level}>${text}</h${level}>`
  })

  // 強調表示を整形
  content = content.replace(/\*\*([^*]+)\*\*/g, "<strong>$1</strong>")
  content = content.replace(/\*([^*]+)\*/g, "<em>$1</em>")

  // 引用を整形
  content = content.replace(/^>\s+(.+)$/gm, "<blockquote>$1</blockquote>")

  promptText.innerHTML = content
}

/**
 * コピーボタンの機能を設定する関数
 */
function setupCopyButton(container) {
  const copyBtn = container.querySelector(".copy-btn")
  if (!copyBtn) return

  copyBtn.addEventListener("click", () => {
    const promptText = container.querySelector("#promptText")
    if (!promptText) return

    // テキストをクリップボードにコピー
    navigator.clipboard
      .writeText(promptText.textContent)
      .then(() => {
        // コピー成功時の表示
        const originalText = copyBtn.innerHTML
        copyBtn.innerHTML = '<i class="bi bi-check"></i> コピー完了！'

        // 成功メッセージを表示
        const alertDiv = document.createElement("div")
        alertDiv.className = "alert alert-success mt-2 copy-alert"
        alertDiv.innerHTML =
          '<i class="bi bi-check-circle"></i> プロンプトをコピーしました。お好みのAIツールに貼り付けて使用できます。'
        container.appendChild(alertDiv)

        // 2秒後に元のテキストに戻し、アラートを削除
        setTimeout(() => {
          copyBtn.innerHTML = originalText
          const alert = container.querySelector(".copy-alert")
          if (alert) {
            alert.remove()
          }
        }, 3000)
      })
      .catch((err) => {
        console.error("クリップボードへのコピーに失敗しました:", err)
        alert("コピーに失敗しました。")
      })
  })
}

/**
 * 画像モーダルの機能を設定する関数
 */
function setupImageModal() {
  // 画像クリック時の処理
  document.querySelectorAll(".zoom-image").forEach((img) => {
    img.addEventListener("click", function () {
      const modalImage = document.getElementById("modalImage")
      const modalTitle = document.getElementById("imageModalLabel")

      if (modalImage && modalTitle) {
        modalImage.src = this.src
        modalTitle.textContent = this.alt || "画像"

        // モーダルを表示
        const imageModal = new bootstrap.Modal(document.getElementById("imageModal"))
        imageModal.show()
      }
    })
  })

  // 画像が見つからない場合のエラーハンドリング
  document.querySelectorAll(".zoom-image").forEach((img) => {
    img.onerror = function () {
      this.style.display = "none"
      const container = this.closest(".image-container")
      if (container) {
        const errorMsg = document.createElement("div")
        errorMsg.className = "alert alert-warning mt-2"
        errorMsg.innerHTML = '<i class="bi bi-exclamation-triangle"></i> 画像を読み込めませんでした。'
        container.appendChild(errorMsg)
      }
    }
  })
}

/**
 * HTMLエスケープ用の関数
 */
function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  }
  return text.replace(/[&<>"']/g, (m) => map[m])
}
