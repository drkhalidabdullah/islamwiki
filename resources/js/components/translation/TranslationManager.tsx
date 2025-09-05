import React, { useState } from 'react';

interface TranslationManagerProps {
  articleId?: number;
  content?: string;
  sourceLanguage?: string;
  targetLanguage?: string;
  onTranslationComplete?: (result: any) => void;
}

interface TranslationResult {
  success: boolean;
  translated_text?: string;
  source_text?: string;
  source_language?: string;
  target_language?: string;
  provider?: string;
  confidence_score?: number;
  from_memory?: boolean;
  error?: string;
}

export const TranslationManager: React.FC<TranslationManagerProps> = ({
  articleId,
  content,
  sourceLanguage = 'en',
  targetLanguage = 'ar',
  onTranslationComplete
}) => {
  const [isTranslating, setIsTranslating] = useState(false);
  const [translationResult, setTranslationResult] = useState<TranslationResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const translateText = async (text: string, sourceLang: string, targetLang: string) => {
    setIsTranslating(true);
    setError(null);

    try {
      const response = await fetch('/api/translation.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          text: text,
          source_language: sourceLang,
          target_language: targetLang
        })
      });

      const result = await response.json();
      
      if (result.success) {
        setTranslationResult(result);
        onTranslationComplete?.(result);
      } else {
        setError(result.error || 'Translation failed');
      }
    } catch (err) {
      setError('Network error occurred');
      console.error('Translation error:', err);
    } finally {
      setIsTranslating(false);
    }
  };

  const translateArticle = async (articleId: number, targetLang: string) => {
    setIsTranslating(true);
    setError(null);

    try {
      const response = await fetch('/api/translation.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          article_id: articleId,
          target_language: targetLang
        })
      });

      const result = await response.json();
      
      if (result.success) {
        setTranslationResult(result);
        onTranslationComplete?.(result);
      } else {
        setError(result.error || 'Article translation failed');
      }
    } catch (err) {
      setError('Network error occurred');
      console.error('Article translation error:', err);
    } finally {
      setIsTranslating(false);
    }
  };

  const handleTranslate = () => {
    if (articleId) {
      translateArticle(articleId, targetLanguage);
    } else if (content) {
      translateText(content, sourceLanguage, targetLanguage);
    }
  };

  return (
    <div className="translation-manager bg-white rounded-lg shadow-md p-6">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-semibold text-gray-800">
          Translation Manager
        </h3>
        <div className="flex items-center space-x-2">
          <span className="text-sm text-gray-600">
            {sourceLanguage.toUpperCase()} → {targetLanguage.toUpperCase()}
          </span>
        </div>
      </div>

      {content && (
        <div className="mb-4">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Source Text
          </label>
          <div className="bg-gray-50 p-3 rounded border text-sm text-gray-800 max-h-32 overflow-y-auto">
            {content}
          </div>
        </div>
      )}

      <div className="flex items-center justify-between mb-4">
        <button
          onClick={handleTranslate}
          disabled={isTranslating || (!content && !articleId)}
          className="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
        >
          {isTranslating ? (
            <div className="flex items-center">
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              Translating...
            </div>
          ) : (
            'Translate'
          )}
        </button>

        {translationResult && (
          <div className="flex items-center text-sm text-gray-600">
            <span className="mr-2">Provider:</span>
            <span className="font-medium">{translationResult.provider}</span>
            {translationResult.confidence_score && (
              <>
                <span className="mx-2">•</span>
                <span>Confidence: {Math.round(translationResult.confidence_score * 100)}%</span>
              </>
            )}
            {translationResult.from_memory && (
              <>
                <span className="mx-2">•</span>
                <span className="text-green-600">From Memory</span>
              </>
            )}
          </div>
        )}
      </div>

      {error && (
        <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
          <p className="text-sm text-red-600">{error}</p>
        </div>
      )}

      {translationResult && translationResult.success && (
        <div className="mb-4">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Translation Result
          </label>
          <div className="bg-green-50 p-3 rounded border text-sm text-gray-800">
            {translationResult.translated_text}
          </div>
        </div>
      )}

      {isTranslating && (
        <div className="mb-4">
          <div className="bg-blue-50 p-3 rounded border">
            <div className="flex items-center">
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
              <span className="text-sm text-blue-600">
                {articleId ? 'Translating article...' : 'Translating text...'}
              </span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default TranslationManager;
