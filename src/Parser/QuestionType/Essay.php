<?php

namespace ExamParser\Parser\QuestionType;

use ExamParser\Constants\QuestionElement;
use ExamParser\Constants\QuestionErrors;

class Essay extends AbstractQuestion
{
    public function convert($questionLines)
    {
        $question = array(
            'type' => 'essay',
            'stem' => '',
            'difficulty' => 'normal',
            'score' => 2.0,
            'analysis' => '',
            'answer' => '',
        );
        $answers = array();
        $preNode = QuestionElement::STEM;
        foreach ($questionLines as $line) {
            //处理答案
            if ($this->matchAnswer($question, $line, $preNode)) {
                continue;
            }
            //处理难度
            if ($this->matchDifficulty($question, $line, $preNode)) {
                continue;
            }
            //处理分数
            if ($this->matchScore($question, $line, $preNode)) {
                continue;
            }

            //处理解析
            if ($this->matchAnalysis($question, $line, $preNode)) {
                continue;
            }

            if (QuestionElement::STEM == $preNode) {
                $question['stem'] .= preg_replace('/^\d{0,5}(\.|、|。|\s)/', '', $line).PHP_EOL;
            }
        }

        $this->checkErrors($question);
        return $question;
    }

    protected function matchAnswer(&$question, $line, &$preNode)
    {
        if (0 === strpos(trim($line), self::ANSWER_SIGNAL)) {
            $answer = str_replace(self::ANSWER_SIGNAL, '', $line);
            $question['answer'] = $answer;
            $preNode = QuestionElement::ANSWER;

            return true;
        }

        return false;
    }

    protected function checkErrors(&$question)
    {
        //判断题干是否有错
        if (empty($question[QuestionElement::STEM])){
            $question['errors'][] = $this->getError(QuestionElement::STEM, QuestionErrors::NO_STEM);
        }

        //判断答案是否有错
        if (empty($question[QuestionElement::ANSWER])) {
            $question['errors'][] = $this->getError(QuestionElement::ANSWER, QuestionErrors::NO_ANSWER);
        }
    }
}
