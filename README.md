# php-doctrine-study

## anemic model VS rich model

### anemic model
anemic model은 데이터와 논리가 분리된 구조.
* anemic model domain example
```java
public class Order {
   private BigDecimal total = BigDecimal.ZERO;
   private List<OrderItem> items = new ArrayList<OrderItem>();

   public BigDecimal getTotal() {
      return total;
   }

   public void setTotal(BigDecimal total) {
      this.total = total;
   }

   public List<OrderItem> getItems() {
      return items;
   }

   public void setItems(List<OrderItem> items) {
      this.items = items;
   }
}

public class OrderItem {
   private BigDecimal price = BigDecimal.ZERO;
   private int quantity;
   private String name;

   public BigDecimal getPrice() {
      return price;
   }

   public void setPrice(BigDecimal price) {
     this.price = price;
   }

   public int getQuantity() {
     return quantity;
   }

   public void setQuantity(int quantity) {
     this.quantity= quantity;
   }

...

}
```

* anemic model service example
```java
public class OrderService {

   public void calculateTotal(Order order) {
      if (order == null) {
          throw new IllegalArgumentException("order must not be null");
      }

      BigDecimal total = BigDecimal.ZERO;
      List<OrderItem> items = order.getItems();

      for (OrderItem orderItem : items) {
          int quantity = orderItem.getQuantity();
          BigDecimal price = orderItem.getPrice();
          BigDecimal itemTotal = price.multiply(new BigDecimal(quantity));
          total = total.add(itemTotal);
     }
     order.setTotal(total);
   }
}
```

* anemic model test
아래의 테스트에서 anemic model의 단점을 볼 수 있다
anemic model은 논리와 데이터가 분리 되어 있기 때문에
항상 합법적인 상태를 보증 할 수 없다.
    BigDecimal totalAfterItemAdd = order.getTotal(); --참조
```java
public class OrderTest {
    /**
    * This test shows that an anemic model can be in an inconsistent state,
    * because it doesn't handle it's state changes. So an anemic model can
    * never guarantee that it is in a legal state. State handling of an anemic
    * model is placed outside that object in classes mostly named "Service",
    * "Helper", "Util", "Manager" etc.
    * <p>
    * <blockquote> The problem with an anemic model is that a client must know
    * in which state the object it uses is and which service methods it has to
    * call if it changes the state of an anemic model to keep the object in a
    * legal state. </blockquote>
    * </p>
    */
    @Test
    public void anAnemicModelCanBeInconsistent() {
        OrderService orderService = new OrderService();
        Order order = new Order();
        BigDecimal total = order.getTotal();

        /*
        * A new order has no items and therefore the total must be zero.
        */
        assertEquals(BigDecimal.ZERO, total);

        OrderItem aGoodBook = new OrderItem();
        aGoodBook.setName("Domain-Driven");
        aGoodBook.setPrice(new BigDecimal("30"));
        aGoodBook.setQuantity(5);

        /*
        * We break encapsulation here as we alter the internal state of the
        * order's item list. This is a common programming pattern when using
        * anemic models.
        */
        order.getItems().add(aGoodBook);

        /*
        * After we added an OrderItem. The Order object is in an illegal state.
        */
        BigDecimal totalAfterItemAdd = order.getTotal();
        BigDecimal expectedTotal = new BigDecimal("150");

        boolean isExpectedTotal = expectedTotal.equals(totalAfterItemAdd);

        /*
        * Of course the order's total can not be the expected total, because
        * anemic models do not handle their state changes.
        */
        assertFalse(isExpectedTotal);

        /*
        * To fix it we have to call the OrderService to re-calculate the total
        * and bring the Order object to a legal state again.
        */
        orderService.calculateTotal(order);

        /*
        * Now the order is in a legal state again.
        */
        BigDecimal totalAfterRecalculation = order.getTotal();
        assertEquals(expectedTotal, totalAfterRecalculation);
    }
}
```
* References : [Source files available though github][https://github.com/link-intersystems/blog/tree/master/anemic-vs-rich-domain-model]