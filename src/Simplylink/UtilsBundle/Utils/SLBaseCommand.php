<?php

namespace Simplylink\UtilsBundle\Utils;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SLBaseCommand
 *
 * BaseCommand for parallax commands, sync with parallax monitor
 * Log errors and information about the command runtime and post it to Parallax monitor
 *
 * @package Simplylink\UtilsBundle\Utils
 */
abstract class SLBaseCommand extends ContainerAwareCommand
{

    protected $errors, $info = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('simplylink:mp_base_command')
            ->setDescription('empty template command');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logInfo('Start running ' . $this->getName() . ' ' . date('d/m/y H:i:s'));

        try{
            $this->executeCommand($input,$output);
        }catch (\Exception $e)
        {
            $this->logError('execute command error: ' . $e->getMessage());
            $output->writeln('execute command error: ' . $e->getMessage());
        }

        $this->logInfo('End running ' . $this->getName() . ' ' . date('d/m/y H:i:s'));
        $response = ['data' => ['errors' => $this->errors, 'info' => $this->info]];

        if(!SLBaseUtils::getApplicationParameter('dev_mode')){
//            $taskLog = new TaskLog($this->setMonitorTaskId(), SLBaseUtils::getApplicationParameter('parallax_monitor_user'), SLBaseUtils::getApplicationParameter('parallax_monitor_password'));
//            $taskLog->createRecord($response);
        }
    }

    /**
     * Add Info
     * @param $infoData
     */
    protected function logInfo($infoData)
    {
        $this->info[] = $infoData;
        $this->devModeLog($infoData);
    }

    /**
     * Add Error
     * @param $errorData
     */
    protected function logError($errorData)
    {
        $this->errors[] = $errorData;
        $this->devModeLog($errorData);
    }

    /**
     * Print message
     * @param $message
     */
    protected function devModeLog($message){
        if (SLBaseUtils::getApplicationParameter('dev_mode')) {
            echo $message . PHP_EOL;
        }
    }

    /**
     * Show progress bar
     *
     * @param OutputInterface $output
     * @param int $total
     * @param string $format
     * @return ProgressBar
     */
    protected function setProgressBar(OutputInterface $output, $total = 0,$format = 'debug')
    {
        $progress = new ProgressBar($output, $total);
        $progress->setFormat($format);
        $progress->start();
        //$progress->advance(); to promote
        return $progress;
    }
    
    /**
     * Execute command process
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected abstract function executeCommand(InputInterface $input, OutputInterface $output);

    /**
     * @return integer
     */
    protected abstract function setMonitorTaskId();



}
