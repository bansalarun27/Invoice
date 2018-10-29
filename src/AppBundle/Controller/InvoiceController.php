<?php

namespace AppBundle\Controller;
use AppBundle\Entity\Invoice;
use AppBundle\Entity\Customer;
use AppBundle\Entity\Invoice_detail;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class InvoiceController extends Controller
{
    /**
     * @Route("/invoice/", name="invoice_page")
     */
    public function indexAction()
    {
       $em=$this->getDoctrine()->getManager();
        $query= $em->createQuery('
          SELECT i.id as id,i.invoiceNumber,i.invoiceDate,i.customerId ,d.description
          ,d.amount,d.vatAmount,d.totalAmount,c.email,c.firstName,c.lastName
          FROM
          AppBundle\Entity\Invoice i
          LEFT JOIN AppBundle\Entity\Customer c WITH i.customerId=c.id
          LEFT JOIN AppBundle\Entity\Invoice_detail d WITH i.id=d.invoiceId
          ');
          $result=$query->execute();    
        return $this->render('invoice/index.html.twig',array('invoice'=>$result));
    }
    /**
     * @Route("/invoice/form/{id}", name="form_page_edit")
     */
    public function editAction($id,Request $request)
    {
             $em=$this->getDoctrine()->getManager();
          $query= $em->createQuery("
              SELECT c.email as email,c.id as id FROM AppBundle\Entity\Customer c
          ");
          $result=$query->execute();
          foreach ($result as $value) {
            $val[$value["email"]]=$value["id"];
          }
          $em->flush();
       $em=$this->getDoctrine()->getManager();
        $query= $em->createQuery("
          SELECT i.id as id,i.invoiceNumber,i.customerId ,d.description
          ,d.amount,d.vatAmount,d.totalAmount , d.id as did
          FROM
          AppBundle\Entity\Invoice i
          LEFT JOIN AppBundle\Entity\Invoice_detail d WITH i.id=d.invoiceId
          where i.id='$id'");
          $result=$query->execute();
        $invoice_detail = new Invoice_detail();
        $invoice = new Invoice();
        $inv[]=$invoice;
        $inv[]=$invoice_detail;
        
        $form = $this->createFormBuilder($inv)
        
            ->add('invoice_number', TextType::class,array('data'=>$result["0"]["invoiceNumber"]))
            ->add('description', TextType::class,array('data'=>$result["0"]["description"]))
             ->add('cutomer', ChoiceType::class,array('choices'=>array($val),'data'=>$result["0"]["customerId"]))
            ->add('amount', TextType::class,array('data'=>$result["0"]["amount"]))
            ->add('vat_amount', TextType::class,array('data'=>$result["0"]["vatAmount"]))
            ->add('save', SubmitType::class, array('label' => 'Submit'))
            ->getForm();

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
          $em=$this->getDoctrine()->getManager();
          $invoice=$em->getRepository('AppBundle:Invoice')->find($id);
           $invoice_number = $form["invoice_number"]->getData();
         
           $invoice->setInvoiceNumber($invoice_number);
           
           $invoice->setCustomerId($form["cutomer"]->getData());
          
           $em->flush();
                     $em=$this->getDoctrine()->getManager();
           $invoice_detail=$em->getRepository('AppBundle:Invoice_detail')->find(array(
            "id"=>$result["0"]["did"]));
            $invoice_detail->setDescription($form["description"]->getData());
           $invoice_detail->setAmount($form["amount"]->getData());
           $invoice_detail->setVatAmount($form["vat_amount"]->getData());
           $invoice_detail->setTotalAmount($form["vat_amount"]->getData()+$form["amount"]->getData());
           $em->flush();
        }

        return $this->render('invoice/form.html.twig', array(
          'invoice'=>$invoice,
            'form' => $form->createView()
        ));
    }
     /**
     * @Route("/invoice/form/", name="form_page")
     */
    public function formAction(Request $request)
    {
        $invoice = new Invoice();
        $invoice_detail = new Invoice_detail();
       
        $inv[]=$invoice;
        $inv[]=$invoice_detail;
        $form = $this->createFormBuilder($inv)
        
            ->add('invoice_number', TextType::class)
            ->add('description', TextType::class)
            ->add('amount', TextType::class)
            ->add('vat_amount', TextType::class)
            ->add('invoice_date', DateType::class, array('widget' => 'single_text'))
            ->add('save', SubmitType::class, array('label' => 'Submit'))
            ->getForm();

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
           $invoice_number = $form["invoice_number"]->getData();
           $invoice_date = $form["invoice_date"]->getData();
           $invoice->setInvoiceNumber($invoice_number);
           $invoice->setInvoiceDate($invoice_date);
           $invoice->setCustomerId("5");
           $invoice_detail->setDescription($form["description"]->getData());
           $invoice_detail->setAmount($form["amount"]->getData());
           $invoice_detail->setVatAmount($form["vat_amount"]->getData());
           $invoice_detail->setTotalAmount($form["vat_amount"]->getData()+$form["amount"]->getData());
           
           $em=$this->getDoctrine()->getManager();
           $em->persist($invoice);
           $em->flush();
           $invoice_detail->setInvoiceId($invoice->getID());
           $em=$this->getDoctrine()->getManager();
           $em->persist($invoice_detail);
           $em->flush();
        }

        return $this->render('invoice/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
